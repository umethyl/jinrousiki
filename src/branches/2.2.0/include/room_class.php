<?php
//-- 個別村クラス --//
class Room {
  public $id;
  public $name;
  public $comment;
  public $game_option = '';
  public $option_role = '';
  public $date;
  public $scene;
  public $status;
  public $system_time;
  public $sudden_death;
  public $view_mode        = false;
  public $dead_mode        = false;
  public $heaven_mode      = false;
  public $log_mode         = false;
  public $watch_mode       = false;
  public $single_log_mode  = false;
  public $single_view_mode = false;
  public $personal_mode    = false;
  public $test_mode        = false;

  function __construct($request = null, $lock = false) {
    if (is_null($request)) return;
    $stack = $request->IsVirtualRoom() ? $request->GetTestRoom() :
      $this->LoadRoom($request->room_no, $lock);
    foreach ($stack as $name => $value) $this->$name = $value;
    $this->ParseOption();
  }

  //指定番地情報を DB から取得
  function LoadRoom($room_no, $lock = false) {
    $stack = RoomDataDB::Get($room_no, $lock);
    if (count($stack) < 1) HTML::OutputResult('村番号エラー', '無効な村番号です: ' . $room_no);
    return $stack;
  }

  //option_role を DB から追加取得
  function LoadOption() {
    $option_role = RQ::Get()->IsVirtualRoom() ? RQ::GetTest()->test_room['option_role'] :
      RoomDB::Fetch('option_role');
    $this->option_role = new OptionParser($option_role);
    $this->option_list = array_merge($this->option_list, array_keys($this->option_role->list));
  }

  //シーンに合わせた投票情報を DB から取得
  function LoadVote($kick = false) {
    if (RQ::Get()->IsVirtualRoom()) {
      if (is_null($vote_list = RQ::GetTest()->vote->{$this->scene})) return null;
    } else {
      $vote_list = RoomDB::GetVote();
    }
    //Text::p($vote_list);

    $stack = array();
    switch ($this->scene) {
    case 'beforegame':
      $type = $kick ? 'KICK_DO' : 'GAMESTART';
      foreach ($vote_list as $list) {
	if ($list['type'] != $type) continue;
	if ($kick) {
	  $stack[$list['user_no']][] = $list['target_no'];
	} else {
	  $stack[] = $list['user_no'];
	}
      }
      break;

    case 'day':
      foreach ($vote_list as $list) {
	$id = $list['user_no'];
	unset($list['user_no']);
	$stack[$id] = $list;
      }
      break;

    case 'night':
      foreach ($vote_list as $list) {
	$id = $list['user_no'];
	unset($list['user_no']);
	$stack[$id][] = $list;
      }
      break;
    }

    $this->vote = $stack;
    return count($this->vote);
  }

  //特殊イベント判定用の情報を DB から取得
  function LoadEvent() {
    if (! $this->IsPlaying()) return null;
    $this->event = new StdClass();
    $this->event->rows = SystemMessageDB::GetEvent();
  }

  //天候判定用の情報を DB から取得
  function LoadWeather($shift = false) {
    if (! $this->IsPlaying()) return null;
    $date = $this->date;
    if (($shift && RQ::Get()->reverse_log) || $this->IsAfterGame()) $date++;
    $result = SystemMessageDB::GetWeather($date);
    $this->event->weather = $result === false ? null : $result; //天候を格納
  }

  //勝敗情報を DB から取得
  function LoadWinner() {
    if (! isset($this->winner)) { //未設定ならキャッシュする
      $this->winner = $this->test_mode ? RQ::GetTest()->winner : RoomDB::Fetch('winner');
    }
    return $this->winner;
  }

  //投票情報をコマンド毎に分割する
  function ParseVote() {
    $stack = array();
    foreach ($this->vote as $id => $vote_stack) {
      if ($this->IsDay()) {
	$stack[$vote_stack['type']][$id] = $vote_stack['target_no'];
      } else {
	foreach ($vote_stack as $list) {
	  $stack[$list['type']][$id] = $list['target_no'];
	}
      }
    }
    return $stack;
  }

  //ゲームオプションの展開処理
  function ParseOption($join = false) {
    $this->game_option = new OptionParser($this->game_option);
    $this->option_role = new OptionParser($this->option_role);
    $this->option_list = $join ?
      array_merge(array_keys($this->game_option->list), array_keys($this->option_role->list)) :
      array_keys($this->game_option->list);

    if ($this->IsRealTime()) {
      $this->real_time = new StdClass();
      $this->real_time->day   = $this->game_option->list['real_time'][0];
      $this->real_time->night = $this->game_option->list['real_time'][1];
    }
  }

  //特殊イベント判定用の情報を取得する
  function GetEvent($force = false) {
    if (! $this->IsPlaying()) return array();
    if ($force || ! isset($this->event)) $this->LoadEvent();
    return $this->event->rows;
  }

  //特殊オプションの配役データ取得
  function GetOptionList($option) {
    return $this->IsOption($option) ?
      ChaosConfig::${$option . '_list'}[$this->option_role->list[$option][0]] : array();
  }

  //オプション判定
  function IsOption($option) { return in_array($option, $this->option_list); }

  //オプショングループ判定
  function IsOptionGroup($option) {
    foreach ($this->option_list as $this_option) {
      if (strpos($this_option, $option) !== false) return true;
    }
    return false;
  }

  //リアルタイム制判定
  function IsRealTime() { return $this->IsOption('real_time'); }

  //身代わり君使用判定
  function IsDummyBoy() { return $this->IsOption('dummy_boy'); }

  //クイズ村判定
  function IsQuiz() { return $this->IsOption('quiz'); }

  //村人置換村グループオプション判定
  function IsReplaceHumanGroup() {
    return $this->IsOption('replace_human') || $this->IsOptionGroup('full_');
  }

  //闇鍋式希望制オプション判定
  function IsChaosWish() {
    return $this->IsOptionGroup('chaos') || $this->IsOption('duel') ||
      $this->IsOption('festival') || $this->IsReplaceHumanGroup() ||
      $this->IsOptionGroup('change_');
  }

  //霊界公開判定
  function IsOpenCast() {
    if (! isset($this->open_cast)) { //未設定ならキャッシュする
      if ($this->IsOption('not_open_cast')) { //常時非公開
	$user = DB::$USER->ByID(1); //身代わり君の蘇生辞退判定
	$this->open_cast = $user->IsDummyBoy() && $user->IsDrop() && DB::$USER->IsOpenCast();
      }
      elseif ($this->IsOption('auto_open_cast')) { //自動公開
	$this->open_cast = DB::$USER->IsOpenCast();
      }
      else { //常時公開
	$this->open_cast = true;
      }
    }
    return $this->open_cast;
  }

  //情報公開判定
  function IsOpenData($virtual = false) {
    return DB::$SELF->IsDummyBoy() ||
      (DB::$SELF->IsDead() && ! $this->single_view_mode && $this->IsOpenCast()) ||
      ($virtual ? $this->IsAfterGame() : ($this->IsFinished() && ! $this->single_view_mode));
  }

  //ゲーム開始前シーン判定
  function IsBeforeGame() { return $this->scene == 'beforegame'; }

  //ゲーム中 (昼) シーン判定
  function IsDay() { return $this->scene == 'day'; }

  //ゲーム中 (夜) シーン判定
  function IsNight() { return $this->scene == 'night'; }

  //ゲーム終了後シーン判定
  function IsAfterGame() { return $this->scene == 'aftergame'; }

  //ゲーム開始前判定
  function IsWaiting() { return $this->status == 'waiting'; }

  //ゲーム中判定 (仮想処理をする為、status では判定しない)
  function IsPlaying() { return $this->IsDay() || $this->IsNight(); }

  //ゲーム終了判定
  function IsFinished() { return $this->status == 'finished'; }

  //当日判定
  function IsDate($date) { return $this->date == $date; }

  //特殊イベント判定
  function IsEvent($type) {
    if (! isset($this->event)) $this->event = new StdClass();
    return isset($this->event->$type) ? $this->event->$type : null;
  }

  //天候セット
  function SetWeather() {
    if ($this->watch_mode || $this->single_view_mode) {
      $this->LoadWeather();
      if (isset($this->event) && WeatherData::Exists($this->event->weather)) {
	$this->event->{WeatherData::GetEvent($this->event->weather)} = true;
      }
    }
    $this->LoadWeather(true);
  }

  //突然死タイマーセット
  function SetSuddenDeath() {
    $this->sudden_death = TimeConfig::SUDDEN_DEATH - RoomDB::GetTime();
  }

  //発言登録
  function Talk($sentence, $action = null, $uname = '', $scene = '', $location = null,
		$font_type = null, $role_id = null, $spend_time = 0) {
    if ($uname == '') $uname = 'system';
    if ($scene == '') {
      $scene = $this->scene;
      if (is_null($location)) $location = 'system';
    }
    if ($this->test_mode) {
      $str = sprintf('Talk: %s: %s: %s: %s: %s', $uname, $scene, $location, $action, $font_type);
      Text::p(Text::Line($sentence), $str);
      return true;
    }

    switch ($scene) {
    case 'beforegame':
    case 'aftergame':
      $table = 'talk_' . $scene;
      break;

    default:
      $table = 'talk';
      break;
    }
    $items  = 'room_no, date, scene, uname, sentence, spend_time, time';
    $values = "{$this->id}, {$this->date}, '{$scene}', '{$uname}', '{$sentence}', {$spend_time}, " .
      "UNIX_TIMESTAMP()";

    if (isset($action)) {
      $items  .= ', action';
      $values .= ", '{$action}'";
    }
    if (isset($location)) {
      $items  .= ', location';
      $values .= ", '{$location}'";
    }
    if (isset($font_type)) {
      $items  .= ', font_type';
      $values .= ", '{$font_type}'";
    }
    if (isset($role_id)) {
      $items  .= ', role_id';
      $values .= ", {$role_id}";
    }
    return DB::Insert($table, $items, $values);
  }

  //発言登録 (ゲーム開始前専用)
  function TalkBeforeGame($sentence, $uname, $handle_name, $color, $font_type = null) {
    if ($this->test_mode) {
      $str = sprintf('Talk: %s: %s: %s: %s', $uname, $handle_name, $color, $font_type);
      Text::p(Text::Line($sentence), $str);
      return true;
    }

    $items  = 'room_no, date, scene, uname, handle_name, color, sentence, time';
    $values = "{$this->id}, 0, '{$this->scene}', '{$uname}', '{$handle_name}', '{$color}', " .
      "'{$sentence}', UNIX_TIMESTAMP()";
    if (isset($font_type)) {
      $items  .= ', font_type';
      $values .= ", '{$font_type}'";
    }
    return DB::Insert('talk_' . $this->scene, $items, $values);
  }

  //超過警告メッセージ登録
  function OvertimeAlert($str) {
    if (RoomDB::IsOvertimeAlert()) return true;
    $this->Talk($str);
    return RoomDB::UpdateOvertimeAlert(true);
  }

  //システムメッセージ登録
  function SystemMessage($str, $type, $add_date = 0) {
    $date = $this->date + $add_date;
    if ($this->test_mode) {
      Text::p("{$type} ({$date}): {$str}", 'SystemMessage');
      if (is_array(RQ::GetTest()->system_message)) {
	RQ::GetTest()->system_message[$date][$type][] = $str;
      }
      return true;
    }
    $items = 'room_no, date, type, message';
    $values = "{$this->id}, {$date}, '{$type}', '{$str}'";
    return DB::Insert('system_message', $items, $values);
  }

  //能力発動結果登録
  function ResultAbility($type, $result, $target = null, $user_no = null) {
    $date = $this->date;
    if ($this->test_mode) {
      Text::p("{$type}: {$result}: {$target}: {$user_no}", 'ResultAbility');
      if (is_array(RQ::GetTest()->result_ability)) {
	$stack = array('user_no' => $user_no, 'target' => $target, 'result' => $result);
	RQ::GetTest()->result_ability[$date][$type][] = $stack;
      }
      return true;
    }
    $items  = 'room_no, date, type';
    $values = "{$this->id}, {$date}, '{$type}'";
    foreach (array('result', 'target', 'user_no') as $data) {
      if (isset($$data)) {
	$items  .= ", {$data}";
	$values .= ", '{$$data}'";
      }
    }
    return DB::Insert('result_ability', $items, $values);
  }

  //システムメッセージ登録
  function ResultDead($name, $type, $result = null) {
    $date = $this->date;
    if ($this->test_mode) {
      Text::p("{$name}: {$type} ({$date}): {$result}", 'ResultDead');
      if (is_array(RQ::GetTest()->result_dead)) {
	$stack = array('type' => $type, 'handle_name' => $name, 'result' => $result);
	RQ::GetTest()->result_dead[] = $stack;
      }
      return true;
    }
    $items = 'room_no, date, scene, type';
    $values = "{$this->id}, {$date}, '{$this->scene}', '{$type}'";
    if (isset($name)) {
      $items  .= ', handle_name';
      $values .= ", '{$name}'";
    }
    if (isset($result)) {
      $items  .= ', result';
      $values .= ", '{$result}'";
    }
    return DB::Insert('result_dead', $items, $values);
  }

  //天候登録
  function EntryWeather($id, $date, $priest = false) {
    $this->SystemMessage($id, 'WEATHER', $date);
    if ($priest) { //祈祷師の処理
      $result = 'prediction_weather_' . WeatherData::GetEvent($id);
      $this->ResultAbility('WEATHER_PRIEST_RESULT', $result);
    }
  }

  //夜にする
  function ChangeNight() {
    $this->scene = 'night';
    if ($this->test_mode) return true;

    RoomDB::UpdateScene();
    return $this->Talk('', 'NIGHT'); //夜がきた通知
  }

  //次の日にする
  function ChangeDate() {
    $this->ShiftScene();
    if ($this->test_mode) return true;

    RoomDB::UpdateScene(true);
    $this->Talk($this->date, 'MORNING'); //夜が明けた通知
    RoomDB::UpdateTime(); //最終書き込みを更新
    return Winner::Check(); //勝敗のチェック
  }

  //夜を飛ばす
  function SkipNight() {
    if ($this->IsEvent('skip_night')) {
      Vote::AggregateNight(true);
      $this->talk(Message::$skip_night);
    }
  }

  //シーンをずらす (主に仮想処理用)
  function ShiftScene($unshift = false) {
    if ($unshift) {
      $this->date--;
      $this->scene = 'night';
    } else {
      $this->date++;
      $this->scene = 'day';
    }
  }

  //背景設定 CSS タグを生成
  function GenerateCSS() {
    if (isset($this->scene)) return HTML::LoadCSS(sprintf('%s/game_%s', JINRO_CSS, $this->scene));
  }

  //村のタイトルタグを生成
  function GenerateTitleTag($log = false) {
    $format = '<%s class="room"><span class="room-name">%s村</span>　[%d番地]' . Text::BR .
      '<span class="room-comment">～%s～</span></%s>' . Text::LF;
    $tag = $log ? 'span' : 'td';

    return sprintf($format, $tag, $this->name, $this->id, $this->comment, $tag);
  }
}

//-- DB アクセス (Room 拡張) --//
class RoomDB {
  const SELECT = 'SELECT %s FROM room';
  const ID     = ' WHERE room_no = %d';
  const DATE   = ' WHERE room_no = %d AND date = %d';
  const LOCK   = ' FOR UPDATE';

  //-- room --//
  //基礎 SQL 取得
  static function Fetch($data, $lock = false) {
    return DB::FetchResult(self::SetID($data, $lock));
  }

  //基礎条件取得
  static function GetID($lock = false) {
    return self::SELECT . self::ID . ($lock ? self::LOCK : '');
  }

  //日付入り条件取得
  static function GetDate() { return self::SELECT . self::Date; }

  //基礎 SQL セット
  static function SetID($data, $lock = false) {
    return sprintf(self::GetID($lock), $data, DB::$ROOM->id);
  }

  //日付入り SQL セット
  static function SetDate() { return sprintf(self::DATE, DB::$ROOM->id, DB::$ROOM->date); }

  //シーン取得
  static function GetScene() { return self::Fetch('scene', true); }

  //経過時間取得
  static function GetTime() { return self::Fetch('UNIX_TIMESTAMP() - last_update_time'); }

  //ゲームオプション取得
  static function GetOption() {
    $query = 'SELECT game_option, option_role, max_user FROM room WHERE room_no = ?';
    DB::Prepare($query, array(DB::$ROOM->id));
    return DB::FetchAssoc(true);
  }

  //超過警告メッセージ出力済み判定
  static function IsOvertimeAlert() {
    $query = 'SELECT overtime_alert FROM room WHERE room_no = ? AND overtime_alert IS FALSE';
    DB::Prepare($query, array(DB::$ROOM->id));
    return DB::Count() < 1;
  }

  //村データ UPDATE
  static function Update($list) {
    $query  = 'UPDATE room SET ';
    $update = array();
    foreach ($list as $key => $value) {
      $update[] = sprintf("%s = '%s'", $key, $value);
    }
    return DB::Execute($query . implode(', ', $update) . sprintf(self::ID, DB::$ROOM->id));
  }

  //最終更新時刻更新
  static function UpdateTime() {
    if (DB::$ROOM->test_mode) return true;
    $query = 'UPDATE room SET last_update_time = UNIX_TIMESTAMP() WHERE room_no = ?';
    DB::Prepare($query, array(DB::$ROOM->id));
    return DB::FetchBool();
  }

  //投票回数更新
  static function UpdateVoteCount($revote = false) {
    if (DB::$ROOM->test_mode) return true;
    $query = 'UPDATE room SET vote_count = vote_count + 1, overtime_alert = FALSE';
    if ($revote) {
      $query .= ', revote_count = revote_count + 1';
    } else {
      $query .= ', last_update_time = UNIX_TIMESTAMP()';
    }
    $query .= ' WHERE room_no = ?';

    DB::Prepare($query, array(DB::$ROOM->id));
    return DB::FetchBool();
  }

  //超過警告メッセージ判定フラグ変更
  static function UpdateOvertimeAlert($bool = false) {
    if (DB::$ROOM->test_mode) return true;
    $format = <<<EOF
UPDATE room SET overtime_alert = %s, last_update_time = UNIX_TIMESTAMP() WHERE room_no = ?
EOF;
    DB::Prepare(sprintf($format, $bool ? 'TRUE' : 'FALSE'), array(DB::$ROOM->id));
    return DB::FetchBool();
  }

  //シーン変更
  static function UpdateScene($date = false) {
    $query = <<<EOF
UPDATE room SET scene = ?, vote_count = ?, overtime_alert = FALSE,
scene_start_time = UNIX_TIMESTAMP()
EOF;
    $list = array(DB::$ROOM->scene, 1);
    if ($date) {
      $query .= ', date = ?, revote_count = ?';
      array_push($list, DB::$ROOM->date, 0);
    }
    $query .= ' WHERE room_no = ?';
    $list[] = DB::$ROOM->id;

    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  //村開始処理
  static function Start() {
    $query = <<<EOF
UPDATE room SET status = ?, date = ?, scene = ?, vote_count = ?,
overtime_alert = FALSE, scene_start_time = UNIX_TIMESTAMP(), start_datetime = NOW()
WHERE room_no = ?
EOF;
    DB::Prepare($query, array('playing', DB::$ROOM->date, DB::$ROOM->scene, 1, DB::$ROOM->id));
    return DB::FetchBool();
  }

  //村終了処理
  static function Finish($winner) {
    $query = <<<EOF
UPDATE room SET status = ?, scene = ?, winner = ?,
scene_start_time = UNIX_TIMESTAMP(), finish_datetime = NOW()
WHERE room_no = ?
EOF;
    DB::Prepare($query, array('finished', 'aftergame', $winner, DB::$ROOM->id));
    return DB::FetchBool();
  }

  //-- player --//
  //プレイヤー情報取得
  static function GetPlayer() {
    $query = 'SELECT id, date, scene, user_no, role FROM player WHERE room_no = ?';
    DB::Prepare($query, array(DB::$ROOM->id));

    $result = new StdClass();
    foreach (DB::FetchAssoc() as $stack) {
      extract($stack);
      $result->role_list[$id] = $role;
      $result->user_list[$user_no][] = $id;
      $result->timeline[$date][$scene][] = $id;
    }
    //Text::p($result, 'Player');
    return $result;
  }

  //-- vote --//
  //投票結果取得
  static function GetVote() {
    $format = 'SELECT %s FROM vote WHERE room_no = ? AND date = ? AND scene = ? AND vote_count = ?';
    switch (DB::$ROOM->scene) {
    case 'beforegame':
    case 'night':
      $data = 'user_no, target_no, type';
      break;

    case 'day': //必要に応じて revote_count を WHERE に足す (不要のはず)
      $data = 'user_no, target_no, vote_number';
      break;

    default:
      return null;
    }
    $query = sprintf($format, $data);
    $list  = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, DB::$ROOM->vote_count);

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //投票リセット
  static function ResetVote() {
    if (DB::$ROOM->test_mode) return true;
    if (! self::UpdateVoteCount()) return false;

    //即処理されるタイプの投票イベントはリセット対象外なので投票回数をスライドさせておく
    if (! DB::$ROOM->IsDate(1)) return true;
    $query = <<<EOF
UPDATE vote SET vote_count = vote_count + 1 WHERE room_no = ? AND date = ? AND type IN (?, ?)
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, 'CUPID_DO', 'DUELIST_DO'));
    return DB::FetchBool();
  }

  //投票データ削除
  static function DeleteVote() {
    if (is_null(DB::$ROOM->id)) return true;

    $query = 'DELETE FROM vote WHERE room_no = ? AND date = ?';
    $list  = array(DB::$ROOM->id, DB::$ROOM->date);
    if (DB::$ROOM->IsDay()) {
      $query .= ' AND type = ? AND revote_count = ?';
      array_push($list, 'VOTE_KILL', DB::$ROOM->revote_count);
    }
    elseif (DB::$ROOM->IsNight()) {
      if (DB::$ROOM->IsDate(1)) {
	$query .= ' AND type NOT IN (?, ?)';
	array_push($list, 'CUPID_DO', 'DUELIST_DO');
      } else {
	$query .= ' AND type NOT IN (?)';
	$list[] = 'VOTE_KILL';
      }
    }

    DB::Prepare($query, $list);
    DB::Execute();
    DB::Optimize('vote');
    return true;
  }
}

//-- DB アクセス (システムメッセージ系拡張) --//
class SystemMessageDB {
  //イベント情報取得
  static function GetEvent() {
    if (DB::$ROOM->test_mode) return DevRoom::GetEvent();
    $query = <<<EOF
SELECT type, message FROM system_message WHERE room_no = ? AND date = ? AND type IN 
EOF;
    $type_list = array('WEATHER', 'EVENT', 'BLIND_VOTE', 'SAME_FACE');
    if (DB::$ROOM->IsDay()) $type_list[] = 'VOTE_DUEL';
    $query .= sprintf('(%s)', implode(',', array_fill(0, count($type_list), '?')));

    DB::Prepare($query, array_merge(array(DB::$ROOM->id, DB::$ROOM->date), $type_list));
    return DB::FetchAssoc();
  }

  //天候情報取得
  static function GetWeather($date) {
    $query = 'SELECT message FROM system_message WHERE room_no = ? AND date = ? AND type = ?';
    DB::Prepare($query, array(DB::$ROOM->id, $date, 'WEATHER'));
    return DB::FetchResult();
  }

  //能力発動結果取得
  static function GetAbility($date, $action, $limit) {
    $query = <<<EOF
SELECT target, result FROM result_ability WHERE room_no = ? AND date = ? AND type = ?
EOF;
    $list = array(DB::$ROOM->id, $date, $action);
    if ($limit) {
      $query .= ' AND user_no = ?';
      $list[] = DB::$SELF->id;
    }
    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //処刑結果取得
  static function GetVote($date) {
    $query = <<<EOF
SELECT count, handle_name, target_name, vote, poll FROM result_vote_kill
WHERE room_no = ? AND date = ? ORDER BY count ASC, id ASC
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, $date));
    return DB::FetchAssoc();
  }

  //処刑結果取得 (クイズ村 GM 専用)
  static function GetQuizVote() {
    $query = 'SELECT target_no FROM vote WHERE room_no = ? AND date = ? AND vote_count = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count));
    return DB::FetchAssoc();
  }

  //死者情報取得
  static function GetDead($shift = false) {
    if (DB::$ROOM->test_mode) return RQ::GetTest()->result_dead;
    $query = <<<EOF
SELECT date, type, handle_name, result FROM result_dead
WHERE room_no = ? AND date = ? AND scene = ?
EOF;
    $list = array(DB::$ROOM->id);
    if ($shift) {
      array_push($list, DB::$ROOM->date - 1, DB::$ROOM->scene);
    } elseif (DB::$ROOM->IsDay()) {
      array_push($list, DB::$ROOM->date - 1, 'night');
    } else {
      array_push($list, DB::$ROOM->date, 'day');
    }

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //遺言取得
  static function GetLastWords($shift = false) {
    $query = 'SELECT handle_name, message FROM result_lastwords WHERE room_no = ? AND date = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date - ($shift ? 0 : 1)));
    return DB::FetchAssoc();
  }
}

//-- DB アクセス (RoomData 拡張) --//
class RoomDataDB {
  //村データ取得
  static function Get($room_no, $lock = false) {
    $query = <<<EOF
SELECT room_no AS id, name, comment, game_option, status, date, scene,
vote_count, revote_count, scene_start_time FROM room WHERE room_no = ?
EOF;
    if ($lock) $query .= ' FOR UPDATE';
    DB::Prepare($query, array($room_no));
    return DB::FetchAssoc(true);
  }

  //終了した村番地を取得
  static function GetFinished($reverse) {
    if (isset(RQ::Get()->name)) {
      $query = <<<EOF
SELECT room_no FROM room INNER JOIN user_entry USING (room_no)
WHERE status = ? AND (uname LIKE ? OR handle_name LIKE ?)
EOF;
      $name = '%' . RQ::Get()->name . '%';
      $list = array('finished', $name, $name);
    }
    else {
      $query = 'SELECT room_no FROM room WHERE status = ?';
      $list  = array('finished');
    }

    $query .= ' ORDER BY room_no ' . ($reverse ? 'DESC' : 'ASC');
    if (RQ::Get()->page != 'all') {
      $view = OldLogConfig::VIEW;
      $query .= sprintf(' LIMIT %d, %d', $view * (RQ::Get()->page - 1), $view);
    }
    DB::Prepare($query, $list);
    return DB::FetchColumn();
  }

  //終了した村数を取得
  static function GetFinishedCount() {
    if (isset(RQ::Get()->name)) {
      $query = <<<EOF
SELECT room_no FROM room INNER JOIN user_entry USING (room_no)
WHERE status = ? AND (uname LIKE ? OR handle_name LIKE ?)
EOF;
      $name = '%' . RQ::Get()->name . '%';
      $list = array('finished', $name, $name);
    }
    else {
      $query = 'SELECT room_no FROM room WHERE status = ?';
      $list  = array('finished');
    }
    DB::Prepare($query, $list);
    return DB::Count();
  }

  //村クラス取得 (進行中)
  static function LoadOpening() {
    $query = <<<EOF
SELECT room_no AS id, name, comment, game_option, option_role, max_user, status
FROM room WHERE status != ? ORDER BY room_no DESC
EOF;
    DB::Prepare($query, array('finished'));
    $stack = DB::FetchClass('room');
    if (count($stack) < 1) die('村一覧の取得に失敗しました');

    $result = array();
    foreach ($stack as $room) {
      $room->ParseOption();
      $result[] = $room;
    }
    return $result;
  }

  //村クラス取得 (終了)
  static function LoadFinished($room_no) {
    $query = <<<EOF
SELECT room_no AS id, name, comment, date, game_option, option_role, max_user, winner,
  establish_datetime, start_datetime, finish_datetime,
  (SELECT COUNT(user_no) FROM user_entry WHERE user_entry.room_no = room.room_no
   AND user_entry.user_no > 0) AS user_count
FROM room WHERE room_no = ? AND status = ?
EOF;
    DB::Prepare($query, array($room_no, 'finished'));
    return DB::FetchClass('Room', true);
  }

  //村クラス取得 (ユーザ登録用)
  static function LoadEntryUser($room_no) {
    $query = <<<EOF
SELECT room_no AS id, date, scene, status, game_option, max_user
FROM room WHERE room_no = ? FOR UPDATE
EOF;
    DB::Prepare($query, array($room_no));
    return DB::FetchClass('Room', true);
  }

  //村クラス取得 (ユーザ登録画面用)
  static function LoadEntryUserPage() {
    $query = <<<EOF
SELECT room_no AS id, name, comment, status, game_option, option_role
FROM room WHERE room_no = ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no));
    return DB::FetchClass('Room', true);
  }

  //村存在判定
  static function Exists() {
    DB::Prepare('SELECT room_no FROM room WHERE room_no = ?', array(RQ::Get()->room_no));
    return DB::Count() > 0;
  }
}
