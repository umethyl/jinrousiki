<?php
//-- 村作成クラス --//
final class RoomEntry {
  //実行
  public static function Execute() {
    RoomEntryValidate::Execute();

    //-- 入力データのエラーチェック --//
    RoomEntryValidate::Input();
    if (false === DB::Lock('room')) { //トランザクション開始
      RoomError::Entry(RoomError::BUSY);
    }

    if (RQ::Enable('change_room')) {
      RoomOptionManager::Stack()->Set('change', true);
      self::LoadExecuteInChange();
    } else {
      RoomEntryValidate::EstablishLimit();
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
      Text::Output(RoomEntryMessage::NOT_ESTABLISH);
      return;
    }

    RoomOptionManager::Stack()->Set('change', RQ::Get(RequestDataGame::ID) > 0);
    if (RoomOptionManager::IsChange()) {
      self::LoadOutputInChange();
      HTML::OutputHeader(RoomManagerMessage::TITLE_CHANGE, 'room_manager');
      HeaderHTML::OutputTitle(RoomManagerMessage::TITLE_CHANGE);
    }
    RoomEntryHTML::Output();
  }

  //データロード (村作成 / オプション変更時)
  private static function LoadExecuteInChange() {
    Session::Login();

    //村情報ロード
    DB::SetRoom(RoomManagerDB::Load(true));
    RoomEntryValidate::RoomStatus();

    //ユーザー情報ロード
    DB::LoadUser();
    if (RQ::Fetch()->max_user < DB::$USER->Count()) {
      RoomError::Change(RoomErrorMessage::CHANGE_MAX_USER);
    }

    //本人情報ロード
    DB::LoadSelf();
    DB::$ROOM->ParseOption(true);
    RoomEntryValidate::SelfStatus();
  }

  //データロード (村作成画面出力 / オプション変更時)
  private static function LoadOutputInChange() {
    Session::Login();

    //村情報ロード
    DB::SetRoom(RoomManagerDB::Load());
    RoomEntryValidate::RoomStatus();

    //ユーザー情報ロード
    DB::LoadUser();
    DB::LoadSelf();
    DB::$ROOM->ParseOption(true);
    RoomEntryValidate::SelfStatus();
  }

  //村作成情報登録
  private static function Store() {
    $room_no = RoomManagerDB::GetNext(); //村番号を取得
    if (false === ServerConfig::DRY_RUN) {
      $game_option = RoomOptionLoader::Get(OptionGroup::GAME);
      $option_role = RoomOptionLoader::Get(OptionGroup::ROLE);
      if (false === RoomManagerDB::Insert($room_no, $game_option, $option_role)) { //村作成
	RoomError::Entry(RoomError::BUSY);
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
	  RoomError::Entry(RoomError::BUSY);
	}
      }
    }

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
	RoomError::Change(RoomErrorMessage::CHANGE_GM_LOGOUT);
      } elseif (false === UserDB::LogoutGM()) {
	RoomError::Entry(RoomError::BUSY);
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
      RoomError::Entry(RoomError::BUSY);
    }

    //システムメッセージ
    $str = Message::SYSTEM . Message::COLON . RoomManagerMessage::CHANGE;
    RoomTalk::StoreBeforeGame($str, DB::$SELF);

    //投票リセット処理 (募集停止のみが変更されている場合はスキップ)
    if (DB::$ROOM->status == $list['status'] ||
	DB::$ROOM->game_option->row != $game_option ||
	DB::$ROOM->option_role->row != $option_role) {
      if (false === RoomDB::UpdateVoteCount()) {
	RoomError::Entry(RoomError::BUSY);
      }
    }
    DB::Commit();
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

//-- 村作成時エラーチェッククラス --//
final class RoomEntryValidate {
  //呼び出しチェック
  public static function Execute() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) { //無効設定
      RoomError::Limit(RoomEntryMessage::NOT_ESTABLISH);
    }

    if (Security::IsInvalidReferer('', ['127.0.0.1', '192.168.'])) { //リファラチェック
      RoomError::Limit(RoomErrorMessage::LIMIT_ACCESS);
    }
  }

  //入力値チェック
  public static function Input() {
    foreach (RoomOptionFilterData::$validate_create_name as $type) { //村の名前・説明
      RoomOptionLoader::LoadPost([$type]);
      if (RQ::Get($type) == '') { //未入力チェック
	RoomError::Entry(RoomError::EMPTY, OptionManager::GenerateCaption($type));
      }

      if (Text::Over(RQ::Get($type), RoomConfig::$$type) ||
	  preg_match(RoomConfig::NG_WORD, RQ::Get($type))) { //文字列チェック
	RoomError::Entry(RoomError::COMMENT, OptionManager::GenerateCaption($type));
      }
    }

    RoomOptionLoader::LoadPost(RoomOptionFilterData::$validate_create_user); //最大人数
    if (false === in_array(RQ::Fetch()->max_user, RoomConfig::$max_user_list)) {
      RoomError::Entry(RoomError::USER);
    }
  }

  //村作成制限チェック
  public static function EstablishLimit() {
    if (ServerConfig::DEBUG_MODE) { //スキップ判定
      return;
    }

    //-- ブラックリスト --//
    if (Security::IsEstablishBlackList()) {
      RoomError::Limit(RoomErrorMessage::LIMIT_BLACK_LIST);
    }

    //-- 村作成パスワード照合 --//
    $room_password = ServerConfig::ROOM_PASSWORD;
    if (isset($room_password)) {
      $str = 'room_password';
      RQ::Fetch()->ParsePostStr($str);
      if (RQ::Get($str) != $room_password) {
	RoomError::Limit(RoomErrorMessage::LIMIT_PASSWORD);
      }
    }

    //-- 最大稼働数制限 --//
    if (RoomManagerDB::CountActive() >= RoomConfig::MAX_ACTIVE_ROOM) {
      RoomError::Wait(RoomErrorMessage::LIMIT_MAX_ROOM);
    }

    //-- 同一ユーザの連続作成制限 --//
    if (RoomManagerDB::CountEstablish() > 0) {
      RoomError::Wait(RoomErrorMessage::LIMIT_ESTABLISH_SELF);
    }

    //-- 連続作成制限 --//
    $time = RoomManagerDB::GetLastEstablish();
    if (isset($time) &&
	Time::Get() - Time::ConvertTimeStamp($time, false) <= RoomConfig::ESTABLISH_WAIT) {
      $str = Text::Join(
	RoomErrorMessage::LIMIT_ESTABLISH_WAIT, RoomErrorMessage::LIMIT_WAIT_TIME
      );
      RoomError::Limit($str);
    }
  }

  //村情報チェック (オプション変更時)
  public static function RoomStatus() {
    if (DB::$ROOM->IsFinished()) {
      RoomError::Change(RoomError::GetRoom(RoomErrorMessage::FINISHED));
    }

    if (false === DB::$ROOM->IsBeforegame()) {
      RoomError::Change(RoomError::GetRoom(RoomErrorMessage::CHANGE_PLAYING));
    }
  }

  //本人情報チェック (オプション変更時)
  public static function SelfStatus() {
    if (false === RoomOptionManager::EnableChange()) {
      $str = sprintf(RoomErrorMessage::CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
      RoomError::Change($str);
    }
  }
}
