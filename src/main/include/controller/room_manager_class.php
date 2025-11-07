<?php
//-- 村作成コントローラー --//
final class RoomManagerController extends JinrouController {
  protected static function Maintenance() {
    if (false === DB::ConnectInHeader()) { //ここでDB接続を行う
      return;
    }

    if (ServerConfig::DISABLE_MAINTENANCE) {
      return;
    }

    if (Loader::IsLoadedFile('index_class')) {
      RoomManagerDB::DieRoom();		//一定時間更新の無い村は廃村にする
      RoomManagerDB::ClearSession();	//終了した村のセッションデータを削除する
    }
  }

  protected static function GetLoadRequest() {
    return 'room_manager';
  }

  protected static function EnableCommand() {
    return true;
  }

  protected static function RunCommand() {
    if (RQ::Fetch()->create_room) {
      self::Create();
    } elseif (RQ::Fetch()->change_room) {
      self::Create();
    } elseif (RQ::Fetch()->describe_room) {
      self::OutputDescribe();
    } elseif (RQ::Get(RequestDataGame::ID) > 0) {
      self::OutputCreate();
    } else {
      self::OutputList();
    }
  }

  protected static function Finish() {
    DB::Disconnect();
  }

  //村作成画面出力
  public static function OutputCreate() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) { //無効判定
      Text::Output(RoomManagerMessage::NOT_ESTABLISH);
      return;
    }

    RoomOptionManager::Stack()->Set('change', RQ::Get(RequestDataGame::ID) > 0);
    if (RoomOptionManager::IsChange()) {
      self::LoadOutputCreateInChange();
      HTML::OutputHeader(RoomManagerMessage::TITLE_CHANGE, 'room_manager');
      HeaderHTML::OutputTitle(RoomManagerMessage::TITLE_CHANGE);
    }
    RoomManagerHTML::OutputCreate();
  }

  //村 (room) の作成
  private static function Create() {
    self::ValidateCreate();

    //-- 入力データのエラーチェック --//
    self::ValidateCreateInput();
    if (false === DB::Lock('room')) { //トランザクション開始
      RoomManagerHTML::OutputResult('busy');
    }

    if (RQ::Enable('change_room')) {
      RoomOptionManager::Stack()->Set('change', true);
      self::LoadCreateInChange();
    } else {
      self::ValidateEstablishLimit();
    }

    //-- ゲームオプションをセット --//
    RoomOptionManager::LoadPost();
    //self::p(); //テスト用

    //-- 登録処理 --//
    if (RQ::Enable('change_room')) { //オプション変更
      self::StoreInChange();
      $str = HTML::GenerateCloseWindow(RoomManagerMessage::CHANGE);
      HTML::OutputResult(RoomManagerMessage::TITLE_CHANGE, $str);
    } else {
      self::Store();
      $url  = ServerConfig::SITE_ROOT;
      $jump = URL::GetJump($url);
      $str  = Text::Join(sprintf(RoomManagerMessage::ENTRY, RQ::Fetch()->room_name), $jump);
      HTML::OutputResult(RoomManagerMessage::TITLE, $str, $url);
    }
  }

  //稼働中の村リスト出力
  private static function OutputList() {
    if (ServerConfig::SECRET_ROOM) { //シークレットテストモード
      return;
    }

    foreach (RoomManagerDB::GetList() as $stack) {
      RoomManagerHTML::OutputRoom($stack);
    }
  }

  //部屋説明出力
  private static function OutputDescribe() {
    //リクエストチェック
    if (RQ::Get(RequestDataGame::ID) < 1) {
      self::OutputDescribeError(Message::INVALID_ROOM);
    }

    //村情報ロード
    DB::SetRoom(RoomManagerDB::Load());
    if (DB::$ROOM->id < 1) {
      self::OutputDescribeError(Message::INVALID_ROOM);
    }
    if (DB::$ROOM->IsFinished()) {
      self::OutputDescribeError(self::GetErrorRoom(RoomManagerMessage::ERROR_FINISHED));
    }

    RoomManagerHTML::OutputDescribe();
  }

  //部屋説明エラー出力
  private static function OutputDescribeError($body) {
    HTML::OutputResult(self::GetErrorTitle(RoomManagerMessage::TITLE_DESCRIBE), $body);
  }

  //データロード (村作成画面出力 / オプション変更時)
  private static function LoadOutputCreateInChange() {
    Session::Login();

    //村情報ロード
    DB::SetRoom(RoomManagerDB::Load());
    self::ValidateRoomCreateInChange();

    //ユーザー情報ロード
    DB::LoadUser();
    DB::LoadSelf();
    DB::$ROOM->ParseOption(true);
    self::ValidateSelfCreateInChange();
  }

  //データロード (村作成 / オプション変更時)
  private static function LoadCreateInChange() {
    Session::Login();

    //村情報ロード
    DB::SetRoom(RoomManagerDB::Load(true));
    self::ValidateRoomCreateInChange();

    //ユーザー情報ロード
    DB::LoadUser();
    if (RQ::Fetch()->max_user < DB::$USER->Count()) {
      $title = sprintf('%s [%s]',
	RoomManagerMessage::TITLE_CHANGE, RoomManagerMessage::ERROR_INPUT
      );
      HTML::OutputResult($title, RoomManagerMessage::ERROR_CHANGE_MAX_USER);
    }

    //本人情報ロード
    DB::LoadSelf();
    DB::$ROOM->ParseOption(true);
    self::ValidateSelfCreateInChange();
  }

  //村情報チェック (オプション変更時)
  private static function ValidateRoomCreateInChange() {
    if (DB::$ROOM->IsFinished()) {
      self::OutputCreateInChangeError(self::GetErrorRoom(RoomManagerMessage::ERROR_FINISHED));
    }
    if (false === DB::$ROOM->IsBeforegame()) {
      self::OutputCreateInChangeError(self::GetErrorRoom(RoomManagerMessage::ERROR_CHANGE_PLAYING));
    }
  }

  //本人情報チェック (オプション変更時)
  private static function ValidateSelfCreateInChange() {
    if (false === RoomOptionManager::EnableChange()) {
      $body = sprintf(RoomManagerMessage::ERROR_CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
      self::OutputCreateInChangeError($body);
    }
  }

  //オプション変更時エラー出力
  private static function OutputCreateInChangeError($body) {
    HTML::OutputResult(self::GetErrorTitle(RoomManagerMessage::TITLE_CHANGE), $body);
  }

  //村作成呼び出しチェック
  private static function ValidateCreate() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) { //無効設定
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::NOT_ESTABLISH);
    }

    if (Security::IsInvalidReferer('', ['127.0.0.1', '192.168.'])) { //リファラチェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_ACCESS);
    }
  }

  //村作成入力値チェック
  private static function ValidateCreateInput() {
    foreach (RoomOptionFilterData::$validate_create_name as $type) { //村の名前・説明
      RoomOptionLoader::LoadPost([$type]);
      if (RQ::Get($type) == '') { //未入力チェック
	RoomManagerHTML::OutputResult('empty', OptionManager::GenerateCaption($type));
      }

      if (Text::Over(RQ::Get($type), RoomConfig::$$type) ||
	  preg_match(RoomConfig::NG_WORD, RQ::Get($type))) { //文字列チェック
	RoomManagerHTML::OutputResult('comment', OptionManager::GenerateCaption($type));
      }
    }

    RoomOptionLoader::LoadPost(RoomOptionFilterData::$validate_create_user); //最大人数
    if (false === in_array(RQ::Fetch()->max_user, RoomConfig::$max_user_list)) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_INPUT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_INPUT_MAX_USER);
    }
  }

  //村作成制限チェック
  private static function ValidateEstablishLimit() {
    if (ServerConfig::DEBUG_MODE) { //スキップ判定
      return;
    }

    //-- ブラックリスト --//
    if (Security::IsEstablishBlackList()) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_BLACK_LIST);
    }

    //-- 村作成パスワード照合 --//
    $room_password = ServerConfig::ROOM_PASSWORD;
    if (isset($room_password)) {
      $str = 'room_password';
      RQ::Fetch()->ParsePostStr($str);
      if (RQ::Get($str) != $room_password) {
	$title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
	HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_PASSWORD);
      }
    }

    //-- 最大稼働数制限 --//
    if (RoomManagerDB::CountActive() >= RoomConfig::MAX_ACTIVE_ROOM) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Join(
	RoomManagerMessage::ERROR_LIMIT_MAX_ROOM, RoomManagerMessage::ERROR_WAIT_FINISH
      );
      HTML::OutputResult($title, $str);
    }

    //-- 同一ユーザの連続作成制限 --//
    if (RoomManagerDB::CountEstablish() > 0) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Join(
	RoomManagerMessage::ERROR_LIMIT_ESTABLISH, RoomManagerMessage::ERROR_WAIT_FINISH
      );
      HTML::OutputResult($title, $str);
    }

    //-- 連続作成制限 --//
    $time = RoomManagerDB::GetLastEstablish();
    if (isset($time) &&
	Time::Get() - Time::ConvertTimeStamp($time, false) <= RoomConfig::ESTABLISH_WAIT) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Join(
	RoomManagerMessage::ERROR_LIMIT_ESTABLISH_WAIT, RoomManagerMessage::ERROR_WAIT_TIME
      );
      HTML::OutputResult($title, $str);
    }
  }

  //村作成情報登録 (オプション変更時)
  private static function StoreInChange() {
    RoomOptionLoader::LoadPost(RoomOptionFilterData::$store_in_change);
    if (RQ::Fetch()->gm_logout) { //GMログアウト処理
      if (DB::$ROOM->IsClosing() || RQ::Fetch()->close_room == RoomStatus::CLOSING) {
	RoomManagerHTML::OutputResult('gm_logout');
      } elseif (false === UserDB::LogoutGM()) {
	RoomManagerHTML::OutputResult('busy');
      }
    }

    $game_option = RoomOptionLoader::Get(OptionGroup::GAME);
    $option_role = RoomOptionLoader::Get(OptionGroup::ROLE);
    $list = [
      'name'		=> RQ::Fetch()->room_name,
      'comment'		=> RQ::Fetch()->room_comment,
      'max_user'	=> RQ::Fetch()->max_user,
      'game_option'	=> $game_option,
      'option_role'	=> $option_role,
      'status'		=> RQ::Fetch()->close_room ? RoomStatus::CLOSING : RoomStatus::WAITING
    ];
    if (false === RoomManagerDB::Update($list)) {
      RoomManagerHTML::OutputResult('busy');
    }

    //システムメッセージ
    $str = Message::SYSTEM . Message::COLON . RoomManagerMessage::CHANGE;
    RoomTalk::StoreBeforeGame($str, DB::$SELF);

    //投票リセット処理 (募集停止のみが変更されている場合はスキップ)
    if (DB::$ROOM->status == $list['status'] ||
	DB::$ROOM->game_option->row != $game_option ||
	DB::$ROOM->option_role->row != $option_role) {
      if (false === RoomDB::UpdateVoteCount()) {
	RoomManagerHTML::OutputResult('busy');
      }
    }
    DB::Commit();
  }

  //村作成情報登録
  private static function Store() {
    $room_no = RoomManagerDB::GetNext(); //村番号を取得
    if (false === ServerConfig::DRY_RUN) {
      $game_option = RoomOptionLoader::Get(OptionGroup::GAME);
      $option_role = RoomOptionLoader::Get(OptionGroup::ROLE);
      if (false === RoomManagerDB::Insert($room_no, $game_option, $option_role)) { //村作成
	RoomManagerHTML::OutputResult('busy');
      }

      //身代わり君を入村させる
      if (RQ::Fetch()->dummy_boy && RoomManagerDB::CountUser($room_no) == 0) {
	$list = [
	  'room_no'	=> $room_no,
	  'user_no'	=> GM::ID,
	  'uname'	=> GM::DUMMY_BOY,
	  'handle_name'	=> RoomOptionManager::Stack()->Get('gm_name'),
	  'password'	=> RoomOptionManager::Stack()->Get('gm_password'),
	  'icon_no'	=> RQ::Fetch()->gerd ? UserIconConfig::GERD : 0,
	  'sex'		=> Sex::MALE,
	  'profile'	=> Message::DUMMY_BOY_PROFILE,
	  'last_words'	=> Message::DUMMY_BOY_LAST_WORDS
	];
	if (false === UserDB::Insert($list)) {
	  RoomManagerHTML::OutputResult('busy');
	}
      }
    }

    //Twitter 投稿
    JinrouTwitter::Send($room_no, RQ::Fetch()->room_name, RQ::Fetch()->room_comment);

    //コミット
    if (CacheConfig::ENABLE) {
      JinrouCacheDB::Clear(); //コミットも内部で行う
    } else {
      DB::Commit();
    }
  }

  //エラーメッセージタイトル取得
  private static function GetErrorTitle($str) {
    return $str . ' ' . Message::ERROR_TITLE;
  }

  //エラーメッセージ対象村取得
  private static function GetErrorRoom($str) {
    return self::GetErrorRoomHeader() . $str;
  }

  //エラーメッセージ対象村ヘッダー取得
  private static function GetErrorRoomHeader() {
    return DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER;
  }

  //テスト用結果表示
  private static function p() {
    if (true !== ServerConfig::DEBUG_MODE) { //スキップ判定
      return;
    }

    HTML::OutputHeader(RoomManagerMessage::TITLE);
    Text::p($_POST, '◆Post');
    Text::p(RoomOptionLoader::Get(OptionGroup::GAME), '◆GameOption');
    Text::p(RoomOptionLoader::Get(OptionGroup::ROLE), '◆OptionRole');
    if (RoomOptionManager::IsChange()) {
      Text::p(DB::$ROOM->game_option, '◆ROOM/game_option');
      Text::p(DB::$ROOM->option_role, '◆ROOM/role_option');
    }
    RQ::p();
    HTML::OutputFooter(true);
  }
}
