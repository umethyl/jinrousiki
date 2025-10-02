<?php
//-- 村作成コントローラー --//
class RoomManager {
  //メンテナンス処理
  static function Maintenance() {
    if (ServerConfig::DISABLE_MAINTENANCE) return; //スキップ判定

    RoomManagerDB::DieRoom(); //一定時間更新の無い村は廃村にする
    //JinrouRSS::Update(); //RSS更新 //テスト中

    RoomManagerDB::ClearSession(); //終了した村のセッションデータを削除する
  }

  //村 (room) の作成
  static function Create() {
    if (ServerConfig::DISABLE_ESTABLISH || DatabaseConfig::DISABLE) {
      HTML::OutputResult('村作成 [制限事項]', '村作成はできません');
    }
    if (Security::CheckReferer('', array('127.0.0.1', '192.168.'))) { //リファラチェック
      HTML::OutputResult('村作成 [入力エラー]', '無効なアクセスです。');
    }

    //-- 入力データのエラーチェック --//
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
      HTML::OutputResult('村作成 [入力エラー]', '無効な最大人数です。');
    }

    if (! DB::Lock('room')) RoomManagerHTML::OutputResult('busy'); //トランザクション開始

    if (RQ::Get()->change_room) {
      OptionManager::$change = true;
      Session::Certify();
      $title = 'オプション変更';

      DB::$ROOM = RoomManagerDB::Load(true); //村情報をロード
      if (DB::$ROOM->IsFinished()) {
	$body = sprintf('%d番地はすでに終了しています', DB::$ROOM->id);
	HTML::OutputResult($title . ' [エラー]', $body);
      }
      if (! DB::$ROOM->IsBeforegame()) {
	$body = sprintf('%d番地はプレイ中です', DB::$ROOM->id);
	HTML::OutputResult($title . ' [エラー]', $body);
      }

      DB::$USER = new UserData(RQ::Get()); //ユーザ情報をロード
      if (RQ::Get()->max_user < DB::$USER->GetUserCount()) {
	HTML::OutputResult($title . ' [入力エラー]', '現在の参加人数より少なくできません。');
      }

      DB::$SELF = DB::$USER->BySession(); //自分の情報をロード
      if (! DB::$SELF->IsDummyBoy()) {
	HTML::OutputResult($title . ' [エラー]', '身代わり君・GM 以外は変更できません');
      }
      DB::$ROOM->ParseOption(true);
    }
    elseif (! ServerConfig::DEBUG_MODE) { //デバッグモード時は村作成制限をスキップ
      //ブラックリストチェック
      if (Security::IsEstablishBlackList()) {
	HTML::OutputResult('村作成 [制限事項]', '村立て制限ホストです。');
      }

      $room_password = ServerConfig::ROOM_PASSWORD;
      if (isset($room_password)) { //パスワードチェック
	$str = 'room_password';
	RQ::Get()->ParsePostStr($str);
	if (RQ::Get()->$str != $room_password) {
	  HTML::OutputResult('村作成 [制限事項]', '村作成パスワードが正しくありません。');
	}
      }

      if (RoomManagerDB::GetActiveCount() >= RoomConfig::MAX_ACTIVE_ROOM) { //最大稼働数チェック
	$str = "現在プレイ中の村の数がこのサーバで設定されている最大値を超えています。<br>\n" .
	  'どこかの村で決着がつくのを待ってから再度登録してください。';
	HTML::OutputResult('村作成 [制限事項]', $str);
      }

      if (RoomManagerDB::GetEstablishCount() > 0) { //同一ユーザの連続作成チェック
	$str = "あなたが立てた村が現在稼働中です。<br>\n" .
	  '立てた村の決着がつくのを待ってから再度登録してください。';
	HTML::OutputResult('村作成 [制限事項]', $str);
      }

      $time = RoomManagerDB::GetLastEstablish(); //連続作成制限チェック
      if (isset($time) &&
	  Time::Get() - Time::ConvertTimeStamp($time, false) <= RoomConfig::ESTABLISH_WAIT) {
	$str = "サーバで設定されている村立て許可時間間隔を経過していません。<br>\n" .
	  'しばらく時間を開けてから再度登録してください。';
	HTML::OutputResult('村作成 [制限事項]', $str);
      }
    }

    //-- ゲームオプションをセット --//
    RoomOption::LoadPost('wish_role', 'real_time');
    if (RQ::Get()->real_time) { //制限時間チェック
      $day   = RQ::Get()->real_time_day;
      $night = RQ::Get()->real_time_night;
      if ($day < 1 || 99 < $day || $night < 1 || 99 < $night) {
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
	$dummy_boy_handle_name = 'GM';
	$dummy_boy_password    = RQ::Get()->gm_password;
      }
      RoomOption::Set(RoomOption::GAME_OPTION, 'dummy_boy');
      RoomOption::Set(RoomOption::GAME_OPTION, 'gm_login');
    }
    else {
      //身代わり君関連のチェック
      if (RQ::Get()->dummy_boy) {
	if (! RQ::Get()->change_room) {
	  $dummy_boy_handle_name = '身代わり君';
	  $dummy_boy_password    = ServerConfig::PASSWORD;
	}
	RoomOption::LoadPost('gerd');
      }
      elseif (RQ::Get()->gm_login) {
	if (! RQ::Get()->change_room) {
	  RQ::Get()->ParsePostStr('gm_password'); //GM ログインパスワードをチェック
	  if (RQ::Get()->gm_password == '') RoomManagerHTML::OutputResult('no_password');
	  $dummy_boy_handle_name = 'GM';
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
	  'sirius_wolf', 'fox', 'child_fox', 'medium');
	if (! RQ::Get()->full_cupid)   RoomOption::LoadPost('cupid');
	if (! RQ::Get()->full_mania)   RoomOption::LoadPost('mania');
	if (! RQ::Get()->perverseness) RoomOption::LoadPost('decide', 'authority');
      }

      if (! RQ::Get()->perverseness) RoomOption::LoadPost('sudden_death');
      RoomOption::LoadPost(
        'liar', 'gentleman', 'passion', 'deep_sleep', 'mind_open', 'blinder', 'critical', 'joker',
	'death_note', 'detective', 'weather', 'festival', 'change_common_selector',
	'change_mad_selector', 'change_cupid_selector');
    }

    $game_option = RoomOption::Get(RoomOption::GAME_OPTION);
    $option_role = RoomOption::Get(RoomOption::ROLE_OPTION);
    //Text::p($_POST, 'Post');
    //Text::p(RQ::Get(), 'RQ');
    //Text::p($game_option, 'GameOption');
    //Text::p($option_role, 'OptionRole');
    //HTML::OutputFooter(true); //テスト用

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
      $str = 'システム：村のオプションを変更しました。';
      DB::$ROOM->TalkBeforeGame($str, DB::$SELF->uname, DB::$SELF->handle_name, DB::$SELF->color);

      RoomDB::UpdateVoteCount(); //投票リセット処理
      DB::Commit();

      $str = <<<EOF
村のオプションを変更しました。<br>
<form method="post" action="#">
<input type="button" value="ウィンドウを閉じる" onClick="window.close()">
</form>

EOF;
      HTML::OutputResult('村オプション変更', $str);
    }

    //登録処理
    $room_no = RoomManagerDB::GetNext(); //村番号を取得
    if (! ServerConfig::DRY_RUN) {
      if (! RoomManagerDB::Insert($room_no, $game_option, $option_role)) { //村作成
	RoomManagerHTML::OutputResult('busy');
      }
	
      //身代わり君を入村させる
      if (RQ::Get()->dummy_boy && RoomManagerDB::GetUserCount($room_no) == 0) {
	if (! DB::InsertUser($room_no, 'dummy_boy', $dummy_boy_handle_name, $dummy_boy_password,
			     1, RQ::Get()->gerd ? UserIconConfig::GERD : 0)) {
	  RoomManagerHTML::OutputResult('busy');
	}
      }
    }

    JinrouTwitter::Send($room_no, RQ::Get()->room_name, RQ::Get()->room_comment); //Twitter 投稿
    //JinrouRSS::Update(); //RSS更新 //テスト中

    if (CacheConfig::ENABLE) {
      DocumentCacheDB::Clean(); //コミットも内部で行う
    } else {
      DB::Commit();
    }

    $format = '%s 村を作成しました。トップページに飛びます。' .
      '切り替わらないなら <a href="%s">ここ</a> 。';
    $str = sprintf($format, RQ::Get()->room_name, ServerConfig::SITE_ROOT);
    HTML::OutputResult('村作成', $str, ServerConfig::SITE_ROOT);
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
      Text::Output('村作成はできません');
      return;
    }

    OptionManager::$change = RQ::Get()->room_no > 0;
    if (OptionManager::$change) {
      Session::Certify();
      $title = 'オプション変更';

      DB::$ROOM = RoomManagerDB::Load(); //村情報をロード
      if (DB::$ROOM->IsFinished()) {
	$body = sprintf('%d番地はすでに終了しています', DB::$ROOM->id);
	HTML::OutputResult($title . ' [エラー]', $body);
      }
      if (! DB::$ROOM->IsBeforegame()) {
	$body = sprintf('%d番地はプレイ中です', DB::$ROOM->id);
	HTML::OutputResult($title . ' [エラー]', $body);
      }

      DB::$USER = new UserData(RQ::Get()); //ユーザ情報をロード
      DB::$SELF = DB::$USER->BySession(); //自分の情報をロード
      if (! DB::$SELF->IsDummyBoy()) {
	HTML::OutputResult($title . ' [エラー]', '身代わり君・GM 以外は変更できません');
      }
      DB::$ROOM->ParseOption(true);

      HTML::OutputHeader($title, 'room_manager');
      Text::Output(sprintf('<h1>%s</h1>', $title));
    }
    RoomManagerHTML::OutputCreate();
  }

  //部屋説明を出力
  static function OutputDescribe() {
    $title = '村情報表示[エラー]';
    if (RQ::Get()->room_no < 1)  HTML::OutputResult($title, '無効な村番地です');
    DB::$ROOM = RoomManagerDB::Load();
    if (DB::$ROOM->id < 1)       HTML::OutputResult($title, '無効な村番地です');
    if (DB::$ROOM->IsFinished()) HTML::OutputResult($title, 'すでにゲーム終了しています');

    HTML::OutputHeader('村情報表示', 'info/info', true);
    $format = <<<EOF
[%d番地]%s村<br>
<div>～%s～ %s</div>

EOF;
    $stack = array('game_option' => DB::$ROOM->game_option,
		   'option_role' => DB::$ROOM->option_role,
		   'max_user'    => DB::$ROOM->max_user);
    RoomOption::Load($stack);
    printf($format, DB::$ROOM->id, DB::$ROOM->name, DB::$ROOM->comment, RoomOption::Generate());
    RoomOption::OutputCaption();
    HTML::OutputFooter();
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
SELECT room_no, name, comment, game_option, option_role, max_user, status
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
  const DELETE = "<a href=\"admin/room_delete.php?room_no=%d\">[削除 (緊急用)]</a>\n";
  const PASSWORD = '<label for="room_password">村作成パスワード</label>：<input type="password" id="room_password" name="room_password" size="20">　';
  const ERROR = "エラーが発生しました。<br>\n以下の項目を再度ご確認ください。<br>\n";

  private static $status = array('waiting' => '募集中', 'playing' => 'プレイ中');

  //村表示
  static function OutputRoom(array $stack) {
    $format = <<<EOF
%s<a href="login.php?room_no=%d">
%s<span>[%d番地]</span>%s村<br>
<div>～%s～ %s</div>
</a><br>

EOF;
    RoomOption::Load($stack);
    extract($stack);
    printf($format,
	   ServerConfig::DEBUG_MODE ? sprintf(self::DELETE, $room_no) : '', $room_no,
	   Image::Room()->Generate($status, self::$status[$status]), $room_no, $name,
	   $comment, RoomOption::Generate());
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
      $submit  = '変更';
    } else {
      $url     = '';
      $command = 'create_room';
      $submit  = '作成';
    }

    //出力
    printf($header, $url, $command);
    OptionForm::Output();
    printf($footer, is_null(ServerConfig::ROOM_PASSWORD) ? '' : self::PASSWORD, $submit);
    if (OptionManager::$change) HTML::OutputFooter();
  }

  //結果出力
  static function OutputResult($type, $str = '') {
    switch ($type) {
    case 'empty':
      $format = "%s<ul>\n<li>%sが記入されていない。</li>\n</ul>\n";
      HTML::OutputResult('村作成 [入力エラー]', sprintf($format, self::ERROR, $str));
      break;

    case 'comment':
      $format = "%s<ul>\n<li>%sの文字数が長すぎる。</li>\n" .
	"<li>%sに入力禁止文字列が含まれている。</li>\n</ul>\n";
      HTML::OutputResult('村作成 [入力エラー]', sprintf($format, self::ERROR, $str, $str));
      break;

    case 'no_password':
      $error = '有効な GM ログインパスワードが設定されていません。';
      HTML::OutputResult('村作成 [入力エラー]', $error);
      break;

    case 'time':
      $error = <<<EOF
<ul>
<li>リアルタイム制の昼・夜の時間を記入していない。</li>
<li>リアルタイム制の昼・夜の時間が 0 以下、または 99 以上である。</li>
<li>リアルタイム制の昼・夜の時間を全角で入力している。</li>
<li>リアルタイム制の昼・夜の時間が数字ではない。</li>
</ul>
EOF;
      HTML::OutputResult('村作成 [入力エラー]', self::ERROR . $error);
      break;

    case 'busy':
      $error = "データベースサーバが混雑しています。<br>\n時間を置いて再度登録してください。";
      HTML::OutputResult('村作成 [データベースエラー]', $erroe);
      break;
    }
  }
}
