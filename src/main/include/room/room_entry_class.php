<?php
//-- 村作成クラス --//
final class RoomEntry {
  //作成
  public static function Create() {
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

  //出力
  public static function Output() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) { //無効判定
      Text::Output(RoomManagerMessage::NOT_ESTABLISH);
      return;
    }

    RoomOptionManager::Stack()->Set('change', RQ::Get(RequestDataGame::ID) > 0);
    if (RoomOptionManager::IsChange()) {
      self::LoadOutputInChange();
      HTML::OutputHeader(RoomManagerMessage::TITLE_CHANGE, 'room_manager');
      HeaderHTML::OutputTitle(RoomManagerMessage::TITLE_CHANGE);
    }
    RoomManagerHTML::OutputCreate();
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

  //データロード (村作成画面出力 / オプション変更時)
  private static function LoadOutputInChange() {
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

  //村情報チェック (オプション変更時)
  private static function ValidateRoomCreateInChange() {
    if (DB::$ROOM->IsFinished()) {
      self::OutputCreateInChangeError(RoomError::GetRoom(RoomManagerMessage::ERROR_FINISHED));
    }
    if (false === DB::$ROOM->IsBeforegame()) {
      self::OutputCreateInChangeError(RoomError::GetRoom(RoomManagerMessage::ERROR_CHANGE_PLAYING));
    }
  }

  //本人情報チェック (オプション変更時)
  private static function ValidateSelfCreateInChange() {
    if (false === RoomOptionManager::EnableChange()) {
      $body = sprintf(RoomManagerMessage::ERROR_CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
      self::OutputCreateInChangeError($body);
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

  //オプション変更時エラー出力
  private static function OutputCreateInChangeError($body) {
    HTML::OutputResult(RoomError::GetTitle(RoomManagerMessage::TITLE_CHANGE), $body);
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
