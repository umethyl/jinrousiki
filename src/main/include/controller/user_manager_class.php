<?php
//-- ユーザ登録コントローラー --//
final class UserManagerController extends JinrouController {
  protected static function Load() {
    Loader::LoadRequest('user_manager');
    DB::Connect();
    Session::Start();
  }

  protected static function EnableCommand() {
    return RQ::Get()->entry;
  }

  protected static function RunCommand() {
    extract(RQ::ToArray()); //引数を展開
    $url = URL::GetRoom('user_manager', $room_no); //ベースバックリンク
    if ($user_no > 0) {
      $url .= URL::GetAddInt(RequestDataUser::ID, $user_no); //登録情報変更モード
    }
    $back_url = Text::BRLF . UserManagerHTML::GenerateError($url); //バックリンク
    if (GameConfig::TRIP && $trip != '') {
      $trip = Text::Trip('#' . $trip); //トリップ変換
      $uname .= $trip;
    } else {
      $trip = ''; //ブラックリストチェック用にトリップを初期化
    }

    //ブラックリストチェック
    if (! ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) {
      self::OutputError(UserManagerMessage::BLACK_LIST_TITLE, UserManagerMessage::BLACK_LIST);
    }

    //記入漏れチェック
    $title = UserManagerMessage::ERROR_INPUT;
    $str   = UserManagerMessage::ERROR_INPUT_TEXT  . $back_url;
    $empty = UserManagerMessage::ERROR_INPUT_EMPTY . $back_url;
    if ($user_no < 1) {
      if ($uname == '') {
	self::OutputError($title, UserManagerMessage::UNAME . $str);
      }
      if ($password  == '') {
	self::OutputError($title, UserManagerMessage::PASSWORD . $str);
      }
    }
    if ($handle_name == '') {
      self::OutputError($title, UserManagerMessage::HANDLE_NAME . $str);
    }
    if (empty($sex)) {
      self::OutputError($title, UserManagerMessage::SEX . $empty);
    }
    if (empty($role)) {
      self::OutputError($title, UserManagerMessage::WISH_ROLE . $empty);
    }
    if (false === is_int($icon_no)) {
      self::OutputError($title, UserManagerMessage::ICON_NUMBER . $empty);
    }

    //文字数制限チェック
    $format = UserManagerMessage::ERROR_TEXT_LIMIT . $back_url;
    $limit_list = [
      ['str'    => $uname,
       'name'   => UserManagerMessage::UNAME,
       'config' => GameConfig::LIMIT_UNAME],
      ['str'    => $handle_name,
       'name'   => UserManagerMessage::HANDLE_NAME,
       'config' => GameConfig::LIMIT_UNAME],
      ['str'    => $profile,
       'name'   => UserManagerMessage::PROFILE,
       'config' => GameConfig::LIMIT_PROFILE]
    ];
    foreach ($limit_list as $limit) {
      if (Text::Over($limit['str'], $limit['config'])) {
	self::OutputError($title, sprintf($format, $limit['name'], $limit['config']));
      }
    }

    //例外チェック
    if ($uname == GM::SYSTEM || $uname == GM::DUMMY_BOY) {
      self::OutputError($title, sprintf(UserManagerMessage::CHECK_UNAME . $back_url, $uname));
    }
    if ($user_no < 1 && self::IsSystemName($handle_name)) {
      $format = UserManagerMessage::CHECK_HANDLE_NAME;
      self::OutputError($title, sprintf($format . $back_url, $handle_name));
    }
    if (! Sex::Exists($sex)) {
      self::OutputError($title, UserManagerMessage::CHECK_SEX . $back_url);
    }
    if ($icon_no < ($user_no > 0 ? 0 : 1) || IconDB::Disable($icon_no)) {
      /* ロック前なのでスキマが存在するが、実用性を考慮してここで判定する */
      self::OutputError($title, UserManagerMessage::CHECK_ICON . $back_url);
    }

    if (! DB::Transaction()) { //トランザクション開始
      self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD . $back_url);
    }

    DB::SetRoom(RoomLoaderDB::LoadEntryUser($room_no)); //現在の村情報を取得 (ロック付き)
    if (DB::$ROOM->IsClosing()) { //募集停止判定
      if ($user_no < 1) { //入村済みならスキップ
	self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::CLOSING);
      }
    } elseif (! DB::$ROOM->IsWaiting()) { //ゲーム開始判定
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
    }
    DB::$ROOM->ParseOption(true);

    //DB から現在のユーザ情報を取得 (ロック付き)
    RQ::LoadRequest('Request', true);
    RQ::Set(RequestDataGame::ID, $room_no);
    RQ::Get('retrieve_type', 'entry_user');
    DB::LoadUser();

    //希望役職チェック
    if (! in_array($role, OptionLoader::Load('wish_role')->GetWishRole())) {
      self::OutputError($title, UserManagerMessage::CHECK_WISH_ROLE . $back_url);
    }

    $user_count = DB::$USER->Count(); //現在の KICK されていない住人の数を取得
    if ($user_no < 1 && $user_count >= DB::$ROOM->max_user) { //定員オーバー判定
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::MAX_USER);
    }

    //重複チェック (比較演算子は大文字・小文字を区別しないのでクエリで直に判定する)
    $footer = Text::BRLF . UserManagerMessage::ERROR_INPUT_FOOTER . $back_url;

    if ($user_no > 0) { //登録情報変更モード
      $title  = UserManagerMessage::CHANGE;
      $target = UserDB::Load($user_no);
      if ($target->session_id != Session::GetID()) {
	self::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      $target->room_no = RQ::Get()->room_no;

      if (! $target->IsDummyBoy() && self::IsSystemName($handle_name)) {
	$format = UserManagerMessage::CHECK_HANDLE_NAME;
	self::OutputError($title, sprintf($format . $back_url, $handle_name));
      }

      if (UserDB::DuplicateName($user_no, $handle_name)) {
	self::OutputError($title, UserManagerMessage::DUPLICATE_NAME . $footer);
      }

      $str   = sprintf(UserManagerMessage::CHANGE_HEADER, $target->handle_name);
      $stack = [];
      if ($target->handle_name != $handle_name) {
	$stack[RequestDataUser::HN] = $handle_name;
	$format = Text::LF . UserManagerMessage::CHANGE_NAME;
	$str .= sprintf($format, $target->handle_name, $handle_name);
      }
      if ($target->icon_no != $icon_no) {
	if (! $target->IsDummyBoy() && $icon_no == 0) {
	  self::OutputError($title, UserManagerMessage::CHECK_ICON . $back_url);
	}
	$stack[RequestDataIcon::ID] = $icon_no;
	$format    = Text::LF . UserManagerMessage::CHANGE_ICON;
	$icon_name = IconDB::GetName($icon_no);
	$str .= sprintf($format, $target->icon_no, $target->icon_name, $icon_no, $icon_name);
      }
      $value_list = [RequestDataUser::SEX, RequestDataUser::PROFILE, RequestDataUser::ROLE];
      foreach ($value_list as $value) {
	if ($target->$value != $$value) {
	  $stack[$value] = $$value;
	}
      }

      if (count($stack) < 1) {
	self::OutputError($title, UserManagerMessage::CHANGE_NONE . $back_url);
      }
      RoomTalk::StoreBeforeGame($str, $target);

      if ($target->UpdateList($stack) && DB::Commit()) {
	self::OutputError($title, HTML::GenerateCloseWindow(UserManagerMessage::CHANGE_SUCCESS));
      } else {
	self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD . $back_url);
      }
    }

    //ユーザ名・村人名
    if (DB::$ROOM->IsOption('necessary_name') && Text::IsPrefix($uname, Message::TRIP)) {
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_UNAME . $back_url);
    }
    if (DB::$ROOM->IsOption('necessary_trip') && ! Text::Search($uname, Message::TRIP)) {
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_TRIP . $back_url);
    }

    if (UserDB::IsKick($uname)) { //キックされた人と同じユーザ名
      self::OutputError($title, UserManagerMessage::ERROR_INPUT_KICK . $footer);
    }

    $title = UserManagerMessage::DUPLICATE; //多重登録判定
    if (UserDB::Duplicate($uname, $handle_name)) { //ユーザ名・村人名重複
      self::OutputError($title, UserManagerMessage::DUPLICATE_NAME . $footer);
    }

    //IP アドレスチェック
    if (! ServerConfig::DEBUG_MODE && GameConfig::LIMIT_IP && UserDB::DuplicateIP()) {
      self::OutputError($title, UserManagerMessage::DUPLICATE_IP);
    }

    //DB にユーザデータを登録
    $list = [
      'room_no'     => $room_no,
      'user_no'     => DB::$USER->CountAll() + 1, //KICK された住人も含めた新しい番号を振る
      'uname'       => $uname,
      'handle_name' => $handle_name,
      'icon_no'     => $icon_no,
      'profile'     => $profile,
      'sex'         => $sex,
      'password'    => $password,
      'role'        => $role
    ];

    if (UserDB::Insert($list)) {
      JinrouCookie::Initialize(); //クッキーの初期化
      RoomTalk::StoreSystem(sprintf(TalkMessage::ENTRY_USER, $handle_name)); //入村メッセージ
      RoomDB::UpdateTime();
      DB::Commit();

      $url = URL::GetRoom('game_frame', $room_no);
      $str = Text::Join(sprintf(UserManagerMessage::ENTRY, ++$user_count), URL::GetJump($url));
      HTML::OutputResult(UserManagerMessage::TITLE, $str, $url);
    } else {
      self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD);
    }
  }

  protected static function Output() {
    if (RQ::Get()->user_no > 0) { //登録情報変更モード
      $stack = UserDB::Get();
      if ($stack['session_id'] != Session::GetID()) {
	self::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      foreach ($stack as $key => $value) {
	if (array_key_exists($key, RQ::Get())) RQ::Set($key, $value);
      }
    }

    DB::SetRoom(RoomLoaderDB::LoadEntryUserPage());
    if (is_null(DB::$ROOM->id)) {
      $str = sprintf(UserManagerMessage::NOT_EXISTS, RQ::Get()->room_no);
      self::OutputError(UserManagerMessage::LOGIN, $str);
    }
    if (DB::$ROOM->IsFinished()) {
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::FINISHED);
    }
    if (DB::$ROOM->IsPlaying()) {
      self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
    }
    DB::$ROOM->ParseOption(true);

    UserManagerHTML::Output();
  }

  protected static function Finish() {
    DB::Disconnect();
  }

  //エラー出力
  private static function OutputError($title, $body) {
    HTML::OutputResult(sprintf(UserManagerMessage::ERROR, $title), $body);
  }

  //システムユーザ名判定
  private static function IsSystemName($name) {
    return $name == Message::SYSTEM || $name == Message::DUMMY_BOY;
  }
}
