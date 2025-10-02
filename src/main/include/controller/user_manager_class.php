<?php
//-- ユーザ登録コントローラー --//
final class UserManagerController extends JinrouController {
  protected static function GetLoadRequest() {
    return 'user_manager';
  }

  protected static function EnableLoadDatabase() {
    return true;
  }

  protected static function LoadSession() {
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
    if (false === ServerConfig::DEBUG_MODE && Security::IsLoginBlackList($trip)) {
      self::OutputError(UserManagerMessage::BLACK_LIST_TITLE, UserManagerMessage::BLACK_LIST);
    }

    try {
      //記入漏れチェック
      if ($user_no < 1) {
	if ($uname == '') {
	  $error = UserManagerMessage::UNAME . UserManagerMessage::ERROR_INPUT_TEXT;
	  throw new UnexpectedValueException($error);
	}
	if ($password  == '') {
	  $error = UserManagerMessage::PASSWORD . UserManagerMessage::ERROR_INPUT_TEXT;
	  throw new UnexpectedValueException($error);
	}
      }
      if ($handle_name == '') {
	$error = UserManagerMessage::HANDLE_NAME . UserManagerMessage::ERROR_INPUT_TEXT;
	throw new UnexpectedValueException($error);
      }
      if (empty($sex)) {
	$error = UserManagerMessage::SEX . UserManagerMessage::ERROR_INPUT_EMPTY;
	throw new UnexpectedValueException($error);
      }
      if (empty($role)) {
	$error = UserManagerMessage::WISH_ROLE . UserManagerMessage::ERROR_INPUT_EMPTY;
	throw new UnexpectedValueException($error);
      }
      if (false === is_int($icon_no)) {
	$error = UserManagerMessage::ICON_NUMBER . UserManagerMessage::ERROR_INPUT_EMPTY;
	throw new UnexpectedValueException($error);
      }

      //文字数制限チェック
      $format = UserManagerMessage::ERROR_TEXT_LIMIT;
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
	  throw new UnexpectedValueException(sprintf($format, $limit['name'], $limit['config']));
	}
      }
      unset($format);

      //例外チェック
      if ($uname == GM::SYSTEM || $uname == GM::DUMMY_BOY) {
	throw new UnexpectedValueException(sprintf(UserManagerMessage::CHECK_UNAME, $uname));
      }
      if ($user_no < 1 && self::IsSystemName($handle_name)) {
	$error = sprintf(UserManagerMessage::CHECK_HANDLE_NAME, $handle_name);
	throw new UnexpectedValueException($error);
      }
      if (false === Sex::Exists($sex)) {
	throw new UnexpectedValueException(UserManagerMessage::CHECK_SEX);
      }
      if ($icon_no < ($user_no > 0 ? 0 : 1) || IconDB::Disable($icon_no)) {
	/* ロック前なのでスキマが存在するが、実用性を考慮してここで判定する */
	throw new UnexpectedValueException(UserManagerMessage::CHECK_ICON);
      }

      if (false === DB::Transaction()) { //トランザクション開始
	self::OutputError(Message::DB_ERROR, Message::DB_ERROR_LOAD . $back_url);
      }

      DB::SetRoom(RoomLoaderDB::LoadEntryUser($room_no)); //現在の村情報を取得 (ロック付き)
      if (DB::$ROOM->IsClosing()) { //募集停止判定
	if ($user_no < 1) { //入村済みならスキップ
	  self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::CLOSING);
	}
      } elseif (false === DB::$ROOM->IsWaiting()) { //ゲーム開始判定
	self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::PLAYING);
      }
      DB::$ROOM->ParseOption(true);

      //DB から現在のユーザ情報を取得 (ロック付き)
      //ここで起動している Request クラスは Load 時の Requset_user_manager
      RQ::Set(RequestDataGame::ID, $room_no);
      RQ::Get('retrieve_type', 'entry_user');
      DB::LoadUser();

      //希望役職チェック
      if (false === ArrayFilter::IsInclude(OptionManager::GetWishRoleList(), $role)) {
	throw new UnexpectedValueException(UserManagerMessage::CHECK_WISH_ROLE);
      }

      $user_count = DB::$USER->Count(); //現在の KICK されていない住人の数を取得
      if ($user_no < 1 && $user_count >= DB::$ROOM->max_user) { //定員オーバー判定
	self::OutputError(UserManagerMessage::LOGIN, UserManagerMessage::MAX_USER);
      }

      //重複チェック (比較演算子は大文字・小文字を区別しないのでクエリで直に判定する)
      $footer = Text::BRLF . UserManagerMessage::ERROR_INPUT_FOOTER;

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
      OptionManager::ValidateUserEntryUname($uname);
      if (UserDB::IsKick($uname)) { //キックされた人と同じユーザ名
	throw new UnexpectedValueException(UserManagerMessage::ERROR_INPUT_KICK . $footer);
      }

      if (UserDB::Duplicate($uname, $handle_name)) { //ユーザ名・村人名重複
	$error = UserManagerMessage::DUPLICATE_NAME . $footer . $back_url;
	self::OutputError(UserManagerMessage::DUPLICATE, $error);
      }

      //IP アドレスチェック
      if (false === ServerConfig::DEBUG_MODE && GameConfig::LIMIT_IP && UserDB::DuplicateIP()) {
	$error = UserManagerMessage::DUPLICATE_IP . $back_url;
	self::OutputError(UserManagerMessage::DUPLICATE, $error);
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
    } catch (UnexpectedValueException $e) {
      self::OutputError(UserManagerMessage::ERROR_INPUT, $e->getMessage() . $back_url);
    } catch (Exception $e) {
      self::OutputError(Message::DB_ERROR, $e->getMessage());
    }
  }

  protected static function Output() {
    if (RQ::Get()->user_no > 0) { //登録情報変更モード
      $stack = UserDB::Get();
      if ($stack['session_id'] != Session::GetID()) {
	self::OutputError(Message::SESSION_ERROR, UserManagerMessage::SESSION);
      }
      RQ::Get()->StorePost($stack);
    }

    DB::SetRoom(RoomLoaderDB::LoadEntryUserPage());
    if (null === DB::$ROOM->id) {
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
