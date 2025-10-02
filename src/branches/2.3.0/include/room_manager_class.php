<?php
//-- 村作成コントローラー --//
class RoomManager {
  //実行処理
  static function Execute() {
    if (RQ::Get()->create_room) {
      Loader::LoadFile('user_class', 'user_icon_class', 'cache_class', 'twitter_class');
      RoomManager::Create();
    }
    elseif (RQ::Get()->change_room) {
      Loader::LoadFile('session_class', 'user_class', 'cache_class');
      RoomManager::Create();
    }
    elseif (RQ::Get()->describe_room) {
      Loader::LoadFile('game_message', 'chaos_config');
      RoomManager::OutputDescribe();
    }
    elseif (RQ::Get()->room_no > 0) {
      Loader::LoadFile('session_class', 'user_class', 'option_form_class');
      RoomManager::OutputCreate();
    }
    else {
      Loader::LoadFile('game_message', 'chaos_config');
      RoomManager::OutputList();
    }
  }

  //メンテナンス処理
  static function Maintenance() {
    if (ServerConfig::DISABLE_MAINTENANCE) return; //スキップ判定

    RoomManagerDB::DieRoom(); //一定時間更新の無い村は廃村にする
    //JinrouRSS::Update(); //RSS更新 //テスト中

    RoomManagerDB::ClearSession(); //終了した村のセッションデータを削除する
  }

  //村 (room) の作成
  static function Create() {
    self::CheckCreate();

    //-- 入力データのエラーチェック --//
    self::CheckCreateInput();
    if (! DB::Lock('room')) RoomManagerHTML::OutputResult('busy'); //トランザクション開始

    if (RQ::Get()->change_room) {
      OptionManager::$change = true;
      Session::Certify();
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
      if (RQ::Get()->max_user < DB::$USER->GetUserCount()) {
	$title = sprintf('%s [%s]', RoomManagerMessage::TITLE_CHANGE,
			 RoomManagerMessage::ERROR_INPUT);
	HTML::OutputResult($title, RoomManagerMessage::ERROR_CHANGE_MAX_USER);
      }

      DB::LoadSelf();
      if (! DB::$SELF->IsDummyBoy()) {
	$body = sprintf(RoomManagerMessage::ERROR_CHANGE_NOT_GM, Message::DUMMY_BOY, Message::GM);
	HTML::OutputResult($title, $body);
      }
      DB::$ROOM->ParseOption(true);
    }
    else {
      self::CheckEstablishLimit();
    }

    //-- ゲームオプションをセット --//
    RoomOption::LoadPost('wish_role', 'real_time');
    if (RQ::Get()->real_time) { //制限時間チェック
      $day   = RQ::Get()->real_time_day;
      $night = RQ::Get()->real_time_night;
      if ($day < 1 || 100 < $day || $night < 1 || 100 < $night) {
	RoomManagerHTML::OutputResult('time');
      }
      RoomOption::Set(RoomOption::GAME_OPTION, sprintf('real_time:%d:%d', $day, $night));
      RoomOption::LoadPost('wait_morning');
    }
    RoomOption::LoadPost(
      'open_vote', 'settle', 'seal_message', 'open_day', 'dummy_boy_selector',
      'not_open_cast_selector', 'perverseness', 'replace_human_selector', 'special_role');
    if (GameConfig::TRIP) RoomOption::LoadPost('necessary_name', 'necessary_trip');
    if (RQ::Get()->change_room) { //変更できないオプションを自動セット
      foreach (array('gm_login', 'dummy_boy') as $option) {
	if (DB::$ROOM->IsOption($option)) {
	  RQ::Get()->$option = true;
	  RoomOption::Set(RoomOption::GAME_OPTION, $option);
	  break;
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
      RoomOption::Set(RoomOption::GAME_OPTION, 'dummy_boy');
      RoomOption::Set(RoomOption::GAME_OPTION, 'gm_login');
    }
    else {
      //身代わり君関連のチェック
      if (RQ::Get()->dummy_boy) {
	if (! RQ::Get()->change_room) {
	  $dummy_boy_handle_name = Message::DUMMY_BOY;
	  $dummy_boy_password    = ServerConfig::PASSWORD;
	}
	RoomOption::LoadPost('gerd');
      }
      elseif (RQ::Get()->gm_login) {
	if (! RQ::Get()->change_room) {
	  RQ::Get()->ParsePostStr('gm_password'); //GM ログインパスワードをチェック
	  if (RQ::Get()->gm_password == '') RoomManagerHTML::OutputResult('no_password');
	  $dummy_boy_handle_name = Message::GM;
	  $dummy_boy_password    = RQ::Get()->gm_password;
	}
	RoomOption::Set(RoomOption::GAME_OPTION, 'dummy_boy');
	RoomOption::LoadPost('gerd');
      }

      //闇鍋モード
      if (RQ::Get()->chaos || RQ::Get()->chaosfull || RQ::Get()->chaos_hyper ||
	  RQ::Get()->chaos_verso) {
	RoomOption::LoadPost('secret_sub_role', 'topping', 'boost_rate', 'chaos_open_cast',
			     'sub_role_limit');
      }
      elseif (! RQ::Get()->duel && ! RQ::Get()->gray_random && ! RQ::Get()->step) { //通常村
	RoomOption::LoadPost(
	  'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'tongue_wolf', 'possessed_wolf',
	  'sirius_wolf', 'mad', 'fox', 'no_fox', 'child_fox', 'depraver', 'medium');
	if (! RQ::Get()->full_cupid)   RoomOption::LoadPost('cupid');
	if (! RQ::Get()->full_mania)   RoomOption::LoadPost('mania');
	if (! RQ::Get()->perverseness) RoomOption::LoadPost('decide', 'authority');
      }

      if (! RQ::Get()->perverseness) RoomOption::LoadPost('sudden_death');
      RoomOption::LoadPost(
	'joker', 'death_note', 'detective', 'weather', 'festival', 'change_common_selector',
	'change_mad_selector', 'change_cupid_selector');
    }
    RoomOption::LoadPost('liar', 'gentleman', 'passion', 'deep_sleep', 'mind_open',
			 'blinder', 'critical');

    $game_option = RoomOption::Get(RoomOption::GAME_OPTION);
    $option_role = RoomOption::Get(RoomOption::ROLE_OPTION);
    //self::p(); //テスト用

    if (RQ::Get()->change_room) { //オプション変更
      $list = array(
	'name'        => RQ::Get()->room_name,
	'comment'     => RQ::Get()->room_comment,
	'max_user'    => RQ::Get()->max_user,
	'game_option' => $game_option,
	'option_role' => $option_role
      );
      if (! RoomDB::Update($list)) RoomManagerHTML::OutputResult('busy');

      //システムメッセージ
      $str = Message::SYSTEM . Message::COLON . RoomManagerMessage::CHANGE;
      DB::$ROOM->TalkBeforeGame($str, DB::$SELF->uname, DB::$SELF->handle_name, DB::$SELF->color);

      RoomDB::UpdateVoteCount(); //投票リセット処理
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
      if (RQ::Get()->dummy_boy && RoomManagerDB::GetUserCount($room_no) == 0) {
	$list = array(
	  'room_no'     => $room_no,
	  'user_no'     => 1,
	  'uname'       => 'dummy_boy',
	  'handle_name' => $dummy_boy_handle_name,
	  'password'    => $dummy_boy_password,
	  'sex'         => 'male',
	  'icon_no'     => RQ::Get()->gerd ? UserIconConfig::GERD : 0,
	  'profile'     => Message::DUMMY_BOY_PROFILE,
	  'last_words'  => Message::DUMMY_BOY_LAST_WORDS
	);
	if (! UserDB::Insert($list)) RoomManagerHTML::OutputResult('busy');
      }
    }

    JinrouTwitter::Send($room_no, RQ::Get()->room_name, RQ::Get()->room_comment); //Twitter 投稿
    //JinrouRSS::Update(); //RSS更新 //テスト中

    if (CacheConfig::ENABLE) {
      DocumentCacheDB::Clean(); //コミットも内部で行う
    } else {
      DB::Commit();
    }

    $str  = sprintf(RoomManagerMessage::ENTRY, RQ::Get()->room_name);
    $jump = sprintf(Message::JUMP, ServerConfig::SITE_ROOT);
    HTML::OutputResult(RoomManagerMessage::TITLE, $str . Text::BR . $jump, ServerConfig::SITE_ROOT);
  }

  //稼働中の村のリストを出力する
  static function OutputList() {
    if (ServerConfig::SECRET_ROOM) return; //シークレットテストモード

    //JinrouRSS::Output(); //RSS //テスト中
    foreach (RoomManagerDB::GetList() as $stack) RoomManagerHTML::OutputRoom($stack);
  }

  //部屋作成画面を出力
  static function OutputCreate() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) {
      Text::Output(RoomManagerMessage::NOT_ESTABLISH);
      return;
    }

    OptionManager::$change = RQ::Get()->room_no > 0;
    if (OptionManager::$change) {
      Session::Certify();
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
      Text::Output(sprintf('<h1>%s</h1>', RoomManagerMessage::TITLE_CHANGE));
    }
    RoomManagerHTML::OutputCreate();
  }

  //部屋説明を出力
  static function OutputDescribe() {
    //エラーチェック
    $title = RoomManagerMessage::TITLE_DESCRIBE . ' ' . Message::ERROR_TITLE;
    if (RQ::Get()->room_no < 1) HTML::OutputResult($title, Message::INVALID_ROOM);

    DB::SetRoom(RoomManagerDB::Load());
    if (DB::$ROOM->id < 1) HTML::OutputResult($title, Message::INVALID_ROOM);
    if (DB::$ROOM->IsFinished()) {
      $body = DB::$ROOM->id . GameMessage::ROOM_NUMBER_FOOTER . RoomManagerMessage::ERROR_FINISHED;
      HTML::OutputResult($title, $body);
    }

    //表示情報セット
    $format = <<<EOF
[%s] %s%s
<div>%s %s</div>
EOF;
    $stack = array('game_option' => DB::$ROOM->game_option,
		   'option_role' => DB::$ROOM->option_role,
		   'max_user'    => DB::$ROOM->max_user);
    RoomOption::Load($stack);

    //出力
    HTML::OutputHeader(RoomManagerMessage::TITLE_DESCRIBE, 'info/info', true);
    printf($format . Text::LF,
	   DB::$ROOM->GenerateNumber(), DB::$ROOM->GenerateName(), Text::BR,
	   DB::$ROOM->GenerateComment(), RoomOption::Generate());
    RoomOption::OutputCaption();
    HTML::OutputFooter();
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

      if (strlen(RQ::Get()->$type) > RoomConfig::$$type ||
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

    if (RoomManagerDB::GetActiveCount() >= RoomConfig::MAX_ACTIVE_ROOM) { //最大稼働数チェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = RoomManagerMessage::ERROR_LIMIT_MAX_ROOM . Text::BRLF .
	RoomManagerMessage::ERROR_WAIT_FINISH;
      HTML::OutputResult($title, $str);
    }

    if (RoomManagerDB::GetEstablishCount() > 0) { //同一ユーザの連続作成チェック
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = RoomManagerMessage::ERROR_LIMIT_ESTABLISH . Text::BRLF .
	RoomManagerMessage::ERROR_WAIT_FINISH;
      HTML::OutputResult($title, $str);
    }

    $time = RoomManagerDB::GetLastEstablish(); //連続作成制限チェック
    if (isset($time) &&
	Time::Get() - Time::ConvertTimeStamp($time, false) <= RoomConfig::ESTABLISH_WAIT) {
      $title = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_LIMIT);
      $str   = RoomManagerMessage::ERROR_LIMIT_ESTABLISH_WAIT . Text::BRLF .
	RoomManagerMessage::ERROR_WAIT_TIME;
      HTML::OutputResult($title, $str);
    }
  }

  //テスト用結果表示
  private static function p() {
    if (! ServerConfig::DEBUG_MODE) return; //スキップ判定

    HTML::OutputHeader(RoomManagerMessage::TITLE);
    Text::p($_POST, '◆Post');
    Text::p(RoomOption::Get(RoomOption::GAME_OPTION), '◆GameOption');
    Text::p(RoomOption::Get(RoomOption::ROLE_OPTION), '◆OptionRole');
    RQ::p();
    HTML::OutputFooter(true);
  }
}

//-- データベースアクセス (RoomManager 拡張) --//
class RoomManagerDB {
  const SELECT = 'SELECT room_no';
  const WHERE  = ' FROM room WHERE status IN (?, ?)';

  private static $status = array('waiting', 'playing');

  //稼働中の村取得
  static function GetList() {
    $query = <<<EOF
SELECT room_no AS id, name, comment, game_option, option_role, max_user, status
FROM room WHERE status IN (?, ?) ORDER BY room_no DESC
EOF;
    DB::Prepare($query, self::$status);
    return DB::FetchAssoc();
  }

  //最終村作成時刻を取得
  static function GetLastEstablish() {
    DB::Prepare('SELECT MAX(establish_datetime)' . self::WHERE, self::$status);
    return DB::FetchResult();
  }

  //現在の稼動数を取得
  static function GetActiveCount() {
    DB::Prepare(self::SELECT . self::WHERE, self::$status);
    return DB::Count();
  }

  //現在の稼動数を取得 (本人作成限定)
  static function GetEstablishCount() {
    $list = array_merge(self::$status, array(Security::GetIP()));
    DB::Prepare(self::SELECT . self::WHERE . ' AND establisher_ip = ?', $list);
    return DB::Count();
  }

  //次の村番号を取得
  static function GetNext() {
    return (int)DB::FetchResult('SELECT MAX(room_no) FROM room') + 1;
  }

  //ユーザ数取得
  static function GetUserCount($room_no) {
    DB::Prepare('SELECT user_no FROM user_entry WHERE room_no = ?', array($room_no));
    return DB::Count();
  }

  //村情報取得
  static function Load($lock = false) {
    $query = <<<EOF
SELECT room_no AS id, name, comment, date, scene, status, game_option, option_role, max_user
FROM room WHERE room_no = ?
EOF;
    if ($lock) $query .= ' FOR UPDATE';
    DB::Prepare($query, array(RQ::Get()->room_no));
    return DB::FetchClass('Room', true);
  }

  //村作成
  static function Insert($room_no, $game_option, $option_role) {
    $query = <<<EOF
INSERT INTO room (room_no, name, comment, max_user, game_option, option_role, status, date, scene,
vote_count, scene_start_time, last_update_time, establisher_ip, establish_datetime)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ?, NOW())
EOF;
    $list = array(
      $room_no, RQ::Get()->room_name, RQ::Get()->room_comment, RQ::Get()->max_user, $game_option,
      $option_role, 'waiting', 0, 'beforegame', 1, Security::GetIP());
    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //廃村処理
  /*
    厳密な処理をするには room のロックが必要になるが、廃村処理はペナルティ的な措置であり
    パフォーマンスの観点から見ても割に合わないと評価してロックは行っていない
  */
  static function DieRoom() {
    $query = <<<EOF
UPDATE room SET status = ?, scene = ?
WHERE status IN (?, ?) AND last_update_time < UNIX_TIMESTAMP() - ?
EOF;
    $list = array('finished', 'aftergame', 'waiting', 'playing', RoomConfig::DIE_ROOM);
    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //セッションクリア
  /*
    厳密な処理をするには room, user_entry のロックが必要になるが、
    仕様上、強制排除措置にあたるので敢えてロックは行わずに処理を行う
  */
  static function ClearSession() {
    $query = <<<EOF
UPDATE user_entry AS u INNER JOIN room AS r USING (room_no)
SET u.session_id = NULL
WHERE u.session_id IS NOT NULL AND r.status = ? AND
  (r.finish_datetime IS NULL OR r.finish_datetime < DATE_SUB(NOW(), INTERVAL ? SECOND))
EOF;
    DB::Prepare($query, array('finished', RoomConfig::KEEP_SESSION));
    return DB::Execute();
  }
}

//-- HTML 生成クラス (RoomManager 拡張) --//
class RoomManagerHTML {
  //村表示
  static function OutputRoom(array $stack) {
    $format = <<<EOF
%s<a href="login.php?room_no=%d">
%s<span>[%s]</span>%s%s
<div>%s %s</div>
</a><br>
EOF;

    $ROOM = new Room();
    $ROOM->LoadData($stack);
    RoomOption::Load($stack);
    if (ServerConfig::DEBUG_MODE) {
      $delete_format = '<a href="admin/room_delete.php?room_no=%d">[%s]</a>';
      $delete = sprintf($delete_format . Text::LF, $ROOM->id, RoomManagerMessage::DELETE);
    }
    else {
      $delete = '';
    }
    $status_list = array('waiting' => RoomManagerMessage::WAITING,
			 'playing' => RoomManagerMessage::PLAYING);

    printf($format . Text::LF,
	   $delete, $ROOM->id,
	   Image::Room()->Generate($ROOM->status, $status_list[$ROOM->status]),
	   $ROOM->GenerateNumber(), $ROOM->GenerateName(), Text::BR,
	   $ROOM->GenerateComment(), RoomOption::Generate());
  }

  //村作成画面表示
  static function OutputCreate() {
    //フォーマットセット
    $header = <<<EOF
<form method="post" action="room_manager.php%s">
<input type="hidden" name="%s" value="on">
<table>
EOF;

    $footer = <<<EOF
<tr><td id="make" colspan="2">%s<input type="submit" value=" %s "></td></tr>
</table>
</form>
EOF;

    //パラメータセット
    if (OptionManager::$change) {
      $url     = sprintf('?room_no=%d', RQ::Get()->room_no);
      $command = 'change_room';
      $submit  = RoomManagerMessage::SUBMIT_CHANGE;
    } else {
      $url     = '';
      $command = 'create_room';
      $submit  = RoomManagerMessage::SUBMIT_CREATE;
    }

    //村作成パスワード
    if (is_null(ServerConfig::ROOM_PASSWORD)) {
      $password = '';
    }
    else {
      $password_format = <<<EOF
<label for="%s">%s</label>%s<input type="password" id="%s" name="%s" size="20">%s
EOF;
      $label = 'room_password';
      $password = sprintf($password_format, $label,
			  RoomManagerMessage::ROOM_PASSWORD, Message::COLON,
			  $label, $label, Message::SPACER);
    }

    //出力
    printf($header . Text::LF, $url, $command);
    OptionForm::Output();
    printf($footer . Text::LF, $password, $submit);
    if (OptionManager::$change) HTML::OutputFooter();
  }

  //結果出力
  static function OutputResult($type, $str = '') {
    $title  = sprintf(RoomManagerMessage::ERROR, RoomManagerMessage::ERROR_INPUT);
    $header = RoomManagerMessage::ERROR_HEADER . Text::BRLF .
      RoomManagerMessage::ERROR_CHECK_LIST . Text::BRLF;

    switch ($type) {
    case 'empty':
      $stack = array($str . RoomManagerMessage::ERROR_INPUT_EMPTY);
      HTML::OutputResult($title, $header . self::GenerateErrorList($stack));
      break;

    case 'comment':
      $stack = array($str . RoomManagerMessage::ERROR_INPUT_LIMIT,
		     $str . RoomManagerMessage::ERROR_INPUT_NG_WORD);
      HTML::OutputResult($title, $header . self::GenerateErrorList($stack));
      break;

    case 'no_password':
      HTML::OutputResult($title, RoomManagerMessage::ERROR_INPUT_PASSWORD);
      break;

    case 'time':
      $error_header = RoomManagerMessage::ERROR_INPUT_REAL_TIME_HEADER;
      $stack = array($error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_EMPTY,
		     $error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_OVER,
		     $error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_EM,
		     $error_header . RoomManagerMessage::ERROR_INPUT_REAL_TIME_NUMBER);
      HTML::OutputResult($title, $header . self::GenerateErrorList($stack));
      break;

    case 'busy':
      $title = sprintf(RoomManagerMessage::ERROR, Message::DB_ERROR);
      HTML::OutputResult($title, Message::DB_ERROR_LOAD);
      break;
    }
  }

  //エラーの説明リストを生成
  private static function GenerateErrorList(array $list) {
    $result = '<ul>' . Text::LF;
    foreach ($list as $str) {
      $result .= sprintf('<li>%s</li>' . Text::LF, $str);
    }
    return $result . '</ul>' . Text::LF;
  }
}
