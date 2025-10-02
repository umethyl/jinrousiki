<?php
//-- 村作成コントローラー --//
class RoomManager {
  //実行
  public static function Execute() {
    self::Load();
    if (RQ::Get()->create_room) {
      Loader::LoadFile('user_class', 'user_icon_class', 'cache_class', 'twitter_class');
      self::Create();
    } elseif (RQ::Get()->change_room) {
      Loader::LoadFile('session_class', 'user_class', 'cache_class');
      self::Create();
    } elseif (RQ::Get()->describe_room) {
      Loader::LoadFile('chaos_config', 'game_message');
      self::OutputDescribe();
    } elseif (RQ::Get()->room_no > 0) {
      Loader::LoadFile('session_class', 'user_class', 'option_form_class');
      self::OutputCreate();
    } else {
      Loader::LoadFile('chaos_config', 'game_message');
      self::OutputList();
    }
    DB::Disconnect();
  }

  //村作成画面出力
  public static function OutputCreate() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) {
      Text::Output(RoomManagerMessage::NOT_ESTABLISH);
      return;
    }

    OptionManager::Stack()->Set('change', RQ::Get()->room_no > 0);
    if (OptionManager::IsChange()) {
      Session::Login();
      DB::SetRoom(RoomManagerDB::Load());

      $title  = RoomManagerMessage::TITLE_CHANGE . ' ' . Message::ERROR_TITLE;
      $header = DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER;
      if (DB::$ROOM->IsFinished()) {
	HTML::OutputResult($title, $header . RoomManagerMessage::ERROR_FINISHED);
      }
      if (! DB::$ROOM->IsBeforegame()) {
	HTML::OutputResult($title, $header . RoomManagerMessage::ERROR_CHANGE_PLAYING);
      }

      DB::LoadUser();
      DB::LoadSelf();
      if (! DB::$SELF->IsDummyBoy()) {
	$body = sprintf(RoomManagerMessage::ERROR_CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
	HTML::OutputResult($title, $body);
      }
      DB::$ROOM->ParseOption(true);

      HTML::OutputHeader(RoomManagerMessage::TITLE_CHANGE, 'room_manager');
      HTML::OutputHeaderTitle(RoomManagerMessage::TITLE_CHANGE);
    }
    RoomManagerHTML::OutputCreate();
  }

  //データロード
  private static function Load() {
    if (! DB::ConnectInHeader()) return false;
    if (Loader::IsLoaded('index_class')) self::Maintenance();
    Loader::LoadRequest('room_manager');
  }

  //メンテナンス処理
  private static function Maintenance() {
    if (ServerConfig::DISABLE_MAINTENANCE) return; //スキップ判定

    RoomManagerDB::DieRoom();		//一定時間更新の無い村は廃村にする
    RoomManagerDB::ClearSession();	//終了した村のセッションデータを削除する
  }

  //村 (room) の作成
  private static function Create() {
    self::CheckCreate();

    //-- 入力データのエラーチェック --//
    self::CheckCreateInput();
    if (! DB::Lock('room')) RoomManagerHTML::OutputResult('busy'); //トランザクション開始

    if (RQ::Get()->change_room) {
      OptionManager::Stack()->Set('change', true);
      Session::Login();
      DB::SetRoom(RoomManagerDB::Load(true));

      $title  = RoomManagerMessage::TITLE_CHANGE . ' ' . Message::ERROR_TITLE;
      $header = DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER;
      if (DB::$ROOM->IsFinished()) {
	HTML::OutputResult($title, $header . RoomManagerMessage::ERROR_FINISHED);
      }
      if (! DB::$ROOM->IsBeforegame()) {
	HTML::OutputResult($title, $header . RoomManagerMessage::ERROR_CHANGE_PLAYING);
      }

      DB::LoadUser();
      if (RQ::Get()->max_user < DB::$USER->Count()) {
	$title = sprintf('%s [%s]',
	  RoomManagerMessage::TITLE_CHANGE, RoomManagerMessage::ERROR_INPUT
	);
	HTML::OutputResult($title, RoomManagerMessage::ERROR_CHANGE_MAX_USER);
      }

      DB::LoadSelf();
      if (! DB::$SELF->IsDummyBoy()) {
	$body = sprintf(RoomManagerMessage::ERROR_CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
	HTML::OutputResult($title, $body);
      }
      DB::$ROOM->ParseOption(true);
    } else {
      self::CheckEstablishLimit();
    }

    //-- ゲームオプションをセット --//
    RoomOption::LoadPost('wish_role', 'real_time');
    if (RQ::Get()->real_time) RoomOption::LoadPost('wait_morning');
    RoomOption::LoadPost(
      'open_vote', 'settle', 'seal_message', 'open_day', 'necessary_name', 'necessary_trip',
      'limit_last_words', 'limit_talk', 'secret_talk', 'dummy_boy_selector',
      'not_open_cast_selector', 'perverseness', 'replace_human_selector', 'special_role'
    );

    if (RQ::Get()->change_room) { //変更できないオプションを自動セット
      foreach (array('gm_login', 'dummy_boy') as $option) {
	if (DB::$ROOM->IsOption($option)) {
	  OptionLoader::Load($option)->LoadPost();
	  if (RQ::Get()->$option) break;
	}
      }
    }

    if (RQ::Get()->quiz) { //クイズ村
      if (! RQ::Get()->change_room) {
	RQ::Get()->ParsePostStr('gm_password'); //GM ログインパスワードをチェック
	if (RQ::Get()->gm_password == '') RoomManagerHTML::OutputResult('no_password');
	$dummy_boy_handle_name = Message::GM;
	$dummy_boy_password    = RQ::Get()->gm_password;
      }
      RoomOption::Set(OptionGroup::GAME, 'dummy_boy');
      RoomOption::Set(OptionGroup::GAME, 'gm_login');
    } else {
      //身代わり君関連のチェック
      if (RQ::Get()->dummy_boy) {
	if (! RQ::Get()->change_room) {
	  $dummy_boy_handle_name = Message::DUMMY_BOY;
	  $dummy_boy_password    = ServerConfig::PASSWORD;
	}
	RoomOption::LoadPost('gerd');
      } elseif (RQ::Get()->gm_login) {
	if (! RQ::Get()->change_room) {
	  RQ::Get()->ParsePostStr('gm_password'); //GM ログインパスワードをチェック
	  if (RQ::Get()->gm_password == '') RoomManagerHTML::OutputResult('no_password');
	  $dummy_boy_handle_name = Message::GM;
	  $dummy_boy_password    = RQ::Get()->gm_password;
	}
	RoomOption::Set(OptionGroup::GAME, 'dummy_boy');
	RoomOption::LoadPost('gerd');
      }

      //闇鍋モード
      if (RQ::Get()->chaos || RQ::Get()->chaosfull || RQ::Get()->chaos_hyper ||
	  RQ::Get()->chaos_verso) { //闇鍋
	RoomOption::LoadPost(
	  'secret_sub_role', 'topping', 'boost_rate', 'chaos_open_cast', 'sub_role_limit'
	);
      } elseif (RQ::Get()->duel || RQ::Get()->gray_random || RQ::Get()->step) {
	//特殊配役
      } else { //通常村
	RoomOption::LoadPost(
	  'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
	  'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'medium'
	);
	if (! RQ::Get()->full_cupid)   RoomOption::LoadPost('cupid');
	if (! RQ::Get()->full_mania)   RoomOption::LoadPost('mania');
	if (! RQ::Get()->perverseness) RoomOption::LoadPost('decide', 'authority');
      }

      if (! RQ::Get()->perverseness) RoomOption::LoadPost('sudden_death');
      RoomOption::LoadPost(
	'joker', 'death_note', 'detective', 'full_weather', 'festival', 'change_common_selector',
	'change_mad_selector', 'change_cupid_selector'
      );
      if (! RQ::Get()->full_weather)   RoomOption::LoadPost('weather');
    }
    RoomOption::LoadPost(
      'no_silence', 'liar', 'gentleman', 'passion', 'deep_sleep', 'mind_open', 'blinder',
      'critical', 'notice_critical'
    );

    $game_option = RoomOption::Get(OptionGroup::GAME);
    $option_role = RoomOption::Get(OptionGroup::ROLE);
    //self::p(); //テスト用

    if (RQ::Get()->change_room) { //オプション変更
      RoomOption::LoadPost('close_room');
      if (RQ::Get()->gm_logout) { //GM ログアウト処理
	if (DB::$ROOM->IsClosing() || RQ::Get()->close_room == RoomStatus::CLOSING) {
	  RoomManagerHTML::OutputResult('gm_logout');
	} elseif (! UserDB::LogoutGM()) {
	  RoomManagerHTML::OutputResult('busy');
	}
      }
      $list = array(
	'name'		=> RQ::Get()->room_name,
	'comment'	=> RQ::Get()->room_comment,
	'max_user'	=> RQ::Get()->max_user,
	'game_option'	=> $game_option,
	'option_role'	=> $option_role,
	'status'	=> RQ::Get()->close_room ? RoomStatus::CLOSING : RoomStatus::WAITING
      );
      if (! RoomManagerDB::Update($list)) RoomManagerHTML::OutputResult('busy');

      //システムメッセージ
      $str = Message::SYSTEM . Message::COLON . RoomManagerMessage::CHANGE;
      DB::$ROOM->TalkBeforeGame($str, DB::$SELF->uname, DB::$SELF->handle_name, DB::$SELF->color);

      //投票リセット処理 (募集停止のみが変更されている場合はスキップ)
      if (DB::$ROOM->status == $list['status'] ||
	  DB::$ROOM->game_option->row != $game_option ||
	  DB::$ROOM->option_role->row != $option_role) {
	if (! RoomDB::UpdateVoteCount()) RoomManagerHTML::OutputResult('busy');
      }
      DB::Commit();

      $str = HTML::GenerateCloseWindow(RoomManagerMessage::CHANGE);
      HTML::OutputResult(RoomManagerMessage::TITLE_CHANGE, $str);
    }

    //登録処理
    $room_no = RoomManagerDB::GetNext(); //村番号を取得
    if (! ServerConfig::DRY_RUN) {
      if (! RoomManagerDB::Insert($room_no, $game_option, $option_role)) { //村作成
	RoomManagerHTML::OutputResult('busy');
      }

      //身代わり君を入村させる
      if (RQ::Get()->dummy_boy && RoomManagerDB::CountUser($room_no) == 0) {
	$list = array(
	  'room_no'	=> $room_no,
	  'user_no'	=> GM::ID,
	  'uname'	=> GM::DUMMY_BOY,
	  'handle_name'	=> $dummy_boy_handle_name,
	  'password'	=> $dummy_boy_password,
	  'sex'		=> Sex::MALE,
	  'icon_no'	=> RQ::Get()->gerd ? UserIconConfig::GERD : 0,
	  'profile'	=> Message::DUMMY_BOY_PROFILE,
	  'last_words'	=> Message::DUMMY_BOY_LAST_WORDS
	);
	if (! UserDB::Insert($list)) RoomManagerHTML::OutputResult('busy');
      }
    }

    JinrouTwitter::Send($room_no, RQ::Get()->room_name, RQ::Get()->room_comment); //Twitter 投稿

    if (CacheConfig::ENABLE) {
      JinrouCacheDB::Clear(); //コミットも内部で行う
    } else {
      DB::Commit();
    }

    $url  = ServerConfig::SITE_ROOT;
    $jump = URL::GetJump($url);
    $str  = Text::Concat(sprintf(RoomManagerMessage::ENTRY, RQ::Get()->room_name), $jump);
    HTML::OutputResult(RoomManagerMessage::TITLE, $str, $url);
  }

  //稼働中の村リスト出力
  private static function OutputList() {
    if (ServerConfig::SECRET_ROOM) return; //シークレットテストモード

    foreach (RoomManagerDB::GetList() as $stack) RoomManagerHTML::OutputRoom($stack);
  }

  //部屋説明出力
  private static function OutputDescribe() {
    //エラーチェック
    $title = RoomManagerMessage::TITLE_DESCRIBE . ' ' . Message::ERROR_TITLE;
    if (RQ::Get()->room_no < 1) HTML::OutputResult($title, Message::INVALID_ROOM);

    DB::SetRoom(RoomManagerDB::Load());
    if (DB::$ROOM->id < 1) HTML::OutputResult($title, Message::INVALID_ROOM);
    if (DB::$ROOM->IsFinished()) {
      $body = DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER . RoomManagerMessage::ERROR_FINISHED;
      HTML::OutputResult($title, $body);
    }
    RoomManagerHTML::OutputDescribe();
  }

  //村作成呼び出しチェック
  private static function CheckCreate() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) { //無効設定
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::NOT_ESTABLISH);
    }

    if (Security::CheckReferer('', array('127.0.0.1', '192.168.'))) { //リファラチェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_ACCESS);
    }
  }

  //村作成入力値チェック
  private static function CheckCreateInput() {
    foreach (array('room_name', 'room_comment') as $type) { //村の名前・説明のデータチェック
      RoomOption::LoadPost($type);
      if (RQ::Get()->$type == '') { //未入力チェック
	RoomManagerHTML::OutputResult('empty', OptionManager::GenerateCaption($type));
      }

      if (Text::Over(RQ::Get()->$type, RoomConfig::$$type) ||
	  preg_match(RoomConfig::NG_WORD, RQ::Get()->$type)) { //文字列チェック
	RoomManagerHTML::OutputResult('comment', OptionManager::GenerateCaption($type));
      }
    }

    RoomOption::LoadPost('max_user'); //最大人数チェック
    if (! in_array(RQ::Get()->max_user, RoomConfig::$max_user_list)) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_INPUT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_INPUT_MAX_USER);
    }
  }

  //村作成制限チェック
  private static function CheckEstablishLimit() {
    if (ServerConfig::DEBUG_MODE) return; //スキップ判定

    //ブラックリストチェック
    if (Security::IsEstablishBlackList()) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_BLACK_LIST);
    }

    $room_password = ServerConfig::ROOM_PASSWORD;
    if (isset($room_password)) { //パスワードチェック
      $str = 'room_password';
      RQ::Get()->ParsePostStr($str);
      if (RQ::Get()->$str != $room_password) {
	$title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
	HTML::OutputResult($title, RoomManagerMessage::ERROR_LIMIT_PASSWORD);
      }
    }

    if (RoomManagerDB::CountActive() >= RoomConfig::MAX_ACTIVE_ROOM) { //最大稼働数チェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Concat(
	RoomManagerMessage::ERROR_LIMIT_MAX_ROOM, RoomManagerMessage::ERROR_WAIT_FINISH
      );
      HTML::OutputResult($title, $str);
    }

    if (RoomManagerDB::CountEstablish() > 0) { //同一ユーザの連続作成チェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Concat(
	RoomManagerMessage::ERROR_LIMIT_ESTABLISH, RoomManagerMessage::ERROR_WAIT_FINISH
      );
      HTML::OutputResult($title, $str);
    }

    $time = RoomManagerDB::GetLastEstablish(); //連続作成制限チェック
    if (isset($time) &&
	Time::Get() - Time::ConvertTimeStamp($time, false) <= RoomConfig::ESTABLISH_WAIT) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = Text::Concat(
	RoomManagerMessage::ERROR_LIMIT_ESTABLISH_WAIT, RoomManagerMessage::ERROR_WAIT_TIME
      );
      HTML::OutputResult($title, $str);
    }
  }

  //テスト用結果表示
  private static function p() {
    if (! ServerConfig::DEBUG_MODE) return; //スキップ判定

    HTML::OutputHeader(RoomManagerMessage::TITLE);
    Text::p($_POST, '◆Post');
    Text::p(RoomOption::Get(OptionGroup::GAME), '◆GameOption');
    Text::p(RoomOption::Get(OptionGroup::ROLE), '◆OptionRole');
    RQ::p();
    HTML::OutputFooter(true);
  }
}
