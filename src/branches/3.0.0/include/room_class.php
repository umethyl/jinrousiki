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
  private $stack;
  private $flag;

  //-- 初期化・基本関数 --//
  public function __construct($request = null, $lock = false) {
    $this->stack = new Stack();
    $this->flag  = new FlagStack();
    if (is_null($request)) return;

    if ($request->IsVirtualRoom()) {
      $stack = $request->GetTestRoom();
    } else {
      $stack = $this->LoadRoom($request->room_no, $lock);
    }
    $this->LoadData($stack);
  }

  //基本情報セット
  public function LoadData(array $stack) {
    foreach ($stack as $name => $value) {
      $this->$name = $value;
    }
    $this->ParseOption();
  }

  //-- データ取得関連 --//
  //勝敗情報を DB から取得
  public function LoadWinner() {
    if (! isset($this->winner)) { //未設定ならキャッシュする
      $this->winner = $this->IsTest() ? RQ::GetTest()->winner : RoomDB::Get('winner');
    }
    return $this->winner;
  }

  //指定番地情報を DB から取得
  private function LoadRoom($room_no, $lock = false) {
    $stack = RoomDataDB::Get($room_no, $lock);
    if (count($stack) < 1) {
      HTML::OutputResult(Message::REQUEST_ERROR, Message::INVALID_ROOM . Message::COLON . $room_no);
    }
    return $stack;
  }

  //-- オプション関連 --//
  //option_role を DB から追加取得
  public function LoadOption() {
    if (RQ::Get()->IsVirtualRoom()) {
      $option_role = RQ::GetTest()->test_room['option_role'];
    } else {
      $option_role = RoomDB::Get('option_role');
    }
    $this->option_role = new OptionParser($option_role);
    $this->option_list = array_merge($this->option_list, array_keys($this->option_role->list));
  }

  //ゲームオプションの展開処理
  public function ParseOption($join = false) {
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

  //特殊オプションの配役データ取得
  public function GetOptionList($option) {
    if ($this->IsOption($option)) {
      return ChaosConfig::${$option . '_list'}[$this->option_role->list[$option][0]];
    } else {
      return array();
    }
  }

  //オプション判定
  public function IsOption($option) {
    return in_array($option, $this->option_list);
  }

  //オプショングループ判定
  public function IsOptionGroup($target_option) {
    foreach ($this->option_list as $option) {
      if (strpos($option, $target_option) !== false) return true;
    }
    return false;
  }

  //リアルタイム制判定
  public function IsRealTime() {
    return $this->IsOption('real_time');
  }

  //身代わり君使用判定
  public function IsDummyBoy() {
    return $this->IsOption('dummy_boy');
  }

  //クイズ村判定
  public function IsQuiz() {
    return $this->IsOption('quiz');
  }

  //村人置換村グループオプション判定
  public function IsReplaceHumanGroup() {
    return $this->IsOption('replace_human') || $this->IsOptionGroup('full_');
  }

  //闇鍋式希望制オプション判定
  public function IsChaosWish() {
    return $this->IsOptionGroup('chaos') || $this->IsOption('duel') ||
      $this->IsOption('festival') || $this->IsReplaceHumanGroup() ||
      $this->IsOptionGroup('change_');
  }

  //-- イベント関連 --//
  //イベント情報を DB から取得
  public function LoadEvent() {
    if (! $this->IsPlaying()) return null;
    $this->Stack()->Set('event', new FlagStack());
    $this->Stack()->Set('event_row', SystemMessageDB::GetEvent());
  }

  //天候判定用の情報を DB から取得
  private function LoadWeather($shift = false) {
    if (! $this->IsPlaying()) return null;
    $date = $this->date;
    if (($shift && RQ::Get()->reverse_log) || $this->IsAfterGame()) $date++;
    $result = SystemMessageDB::GetWeather($date);
    if ($result !== false) {
      $this->Stack()->Set('weather', $result); //天候を格納
    } else {
      $this->Stack()->Clear('weather'); //ログ用に初期化する
    }
  }

  //イベント情報初期化
  public function InitEvent() {
    if ($this->Stack()->IsEmpty('event')) $this->Stack()->Set('event', new FlagStack());
  }

  //イベント情報消去
  public function ResetEvent() {
    $this->Stack()->Clear('event');
    $this->Stack()->Clear('event_row');
  }

  //イベント情報取得
  public function GetEvent($force = false) {
    if (! $this->IsPlaying()) return array();
    if ($force || $this->Stack()->IsEmpty('event_row')) $this->LoadEvent();
    return $this->Stack()->Get('event_row');
  }

  //イベント判定
  public function IsEvent($type) {
    $this->InitEvent();
    return $this->Stack()->Get('event')->Get($type);
  }

  //天候セット (ログ用)
  public function SetWeather() {
    if ($this->IsOn('watch') || $this->IsOn('single')) {
      $this->LoadWeather();
      $stack = $this->Stack();
      if ($stack->Exists('weather') && WeatherData::Exists($stack->Get('weather'))) {
	$this->InitEvent();
	$stack->Get('event')->On(WeatherData::GetEvent($stack->Get('weather')));
      }
    }
    $this->LoadWeather(true);
  }

  //-- シーン判定関連 --//
  //ゲーム開始前シーン判定
  public function IsBeforeGame() {
    return $this->scene == RoomScene::BEFORE;
  }

  //ゲーム中 (昼) シーン判定
  public function IsDay() {
    return $this->scene == RoomScene::DAY;
  }

  //ゲーム中 (夜) シーン判定
  public function IsNight() {
    return $this->scene == RoomScene::NIGHT;
  }

  //ゲーム終了後シーン判定
  public function IsAfterGame() {
    return $this->scene == RoomScene::AFTER;
  }

  //ゲーム開始前判定
  public function IsWaiting() {
    return $this->status == RoomStatus::WAITING;
  }

  //ゲーム中判定 (仮想処理をする為、status では判定しない)
  public function IsPlaying() {
    return $this->IsDay() || $this->IsNight();
  }

  //ゲーム終了判定
  public function IsFinished() {
    return $this->status == RoomStatus::FINISHED;
  }

  //当日判定
  public function IsDate($date) {
    return $this->date == $date;
  }

  //-- スタック関連 --//
  //スタック取得
  public function Stack() {
    return $this->stack;
  }

  //-- モード判定関連 --//
  //フラグスタック取得
  public function Flag() {
    return $this->flag;
  }

  //フラグセット
  public function SetFlag() {
    foreach (func_get_args() as $mode) {
      $this->Flag()->On($mode);
    }
  }

  //ON 判定
  public function IsOn($mode) {
    return $this->Flag()->Get($mode);
  }

  //OFF 判定
  public function IsOff($mode) {
    return ! $this->IsOn($mode);
  }

  //テストモード判定
  public function IsTest() {
    return $this->IsOn('test');
  }

  //-- 情報公開判定関連 --//
  //霊界公開判定
  public function IsOpenCast() {
    $data = 'open_cast';
    if ($this->Flag()->IsEmpty($data)) { //未設定ならキャッシュする
      if ($this->IsOption('not_open_cast')) { //常時非公開
	$user = DB::$USER->ByID(GM::ID); //身代わり君の蘇生辞退判定
	$flag = $user->IsDummyBoy() && $user->IsDrop() && DB::$USER->IsOpenCast();
      }
      elseif ($this->IsOption('auto_open_cast')) { //自動公開
	$flag = DB::$USER->IsOpenCast();
      }
      else { //常時公開
	$flag = true;
      }
      $this->Flag()->Set($data, $flag);
    }
    return $this->Flag()->Get($data);
  }

  //情報公開判定
  /*
    + ゲーム終了後は全て表示
    + 霊界表示オン状態の死者には全て表示
    + 霊界表示オフ状態は観戦者と同じ (投票情報は表示しない)
  */
  public function IsOpenData($virtual = false) {
    return DB::$SELF->IsDummyBoy() ||
      (DB::$SELF->IsDead() && $this->IsOff('single') && $this->IsOpenCast()) ||
      ($virtual ? $this->IsAfterGame() : ($this->IsFinished() && $this->IsOff('single')));
  }

  //-- 発言関連 --//
  //発言制限情報を取得
  public function GetLimitTalk() {
    $data = 'limit_talk';
    if ($this->Stack()->IsEmpty($data)) { //未設定ならキャッシュする
      $this->Stack()->Set($data, $this->game_option->list[$data][0]);
    }
    return $this->Stack()->Get($data);
  }

  //発言登録
  public function Talk($sentence, $action = null, $uname = '', $scene = '', $location = null,
		       $font_type = null, $role_id = null, $spend_time = 0) {
    if ($uname == '') $uname = GM::SYSTEM;
    if ($scene == '') {
      $scene = $this->scene;
      if (is_null($location)) $location = TalkLocation::SYSTEM;
    }
    if ($this->IsTest()) {
      $str = sprintf('Talk: %s: %s: %s: %s: %s', $uname, $scene, $location, $action, $font_type);
      Text::p(Text::Line($sentence), $str);
      return true;
    }

    switch ($scene) {
    case RoomScene::BEFORE:
    case RoomScene::AFTER:
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
  public function TalkBeforeGame($sentence, $uname, $handle_name, $color, $font_type = null) {
    if ($this->IsTest()) {
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

  //-- 時間関連 --//
  //突然死タイマーセット
  public function SetSuddenDeath() {
    $this->sudden_death = TimeConfig::SUDDEN_DEATH - RoomDB::GetTime();
  }

  //超過警告メッセージ登録
  public function OvertimeAlert($str) {
    if (RoomDB::IsOvertimeAlert()) return true;
    $this->Talk($str);
    return RoomDB::UpdateOvertimeAlert(true);
  }

  //-- 投票関連 --//
  //シーンに合わせた投票情報を DB から取得
  public function LoadVote($kick = false) {
    if (RQ::Get()->IsVirtualRoom()) {
      $vote_list = RQ::GetTest()->vote->{$this->scene};
      if (is_null($vote_list)) return null;
    } else {
      $vote_list = RoomDB::GetVote();
    }
    //Text::p($vote_list, '◆vote_list');

    $stack = array();
    switch ($this->scene) {
    case RoomScene::BEFORE:
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

    case RoomScene::DAY:
      foreach ($vote_list as $list) {
	$id = $list['user_no'];
	unset($list['user_no']);
	$stack[$id] = $list;
      }
      break;

    case RoomScene::NIGHT:
      foreach ($vote_list as $list) {
	$id = $list['user_no'];
	unset($list['user_no']);
	$stack[$id][] = $list;
      }
      break;
    }

    $this->Stack()->Set('vote', $stack);
    return count($stack);
  }

  //投票情報をコマンド毎に分割する
  public function ParseVote() {
    $stack = array();
    foreach ($this->Stack()->Get('vote') as $id => $vote_stack) {
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

  //-- システムメッセージ関連 --//
  //システムメッセージ登録
  public function SystemMessage($str, $type, $add_date = 0) {
    $date = $this->date + $add_date;
    if ($this->IsTest()) {
      Text::p("{$type} ({$date}): {$str}", 'SystemMessage');
      if (is_array(RQ::GetTest()->system_message)) {
	RQ::GetTest()->system_message[$date][$type][] = $str;
      }
      return true;
    }

    $items  = 'room_no, date, type, message';
    $values = "{$this->id}, {$date}, '{$type}', '{$str}'";
    return DB::Insert('system_message', $items, $values);
  }

  //死亡情報登録
  public function ResultDead($name, $type, $result = null) {
    $date = $this->date;
    if ($this->IsTest()) {
      Text::p("{$name}: {$type} ({$date}): {$result}", 'ResultDead');
      if (is_array(RQ::GetTest()->result_dead)) {
	$stack = array('type' => $type, 'handle_name' => $name, 'result' => $result);
	RQ::GetTest()->result_dead[] = $stack;
      }
      return true;
    }

    $items  = 'room_no, date, scene, type';
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

  //能力発動結果登録
  public function ResultAbility($type, $result, $target = null, $user_no = null) {
    $date = $this->date;
    if ($this->IsTest()) {
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

  //天候登録
  public function EntryWeather($id, $date, $priest = false) {
    $this->SystemMessage($id, 'WEATHER', $date);
    if ($priest) { //祈祷師の処理
      $result = 'prediction_weather_' . WeatherData::GetEvent($id);
      $this->ResultAbility('WEATHER_PRIEST_RESULT', $result);
    }
  }

  //-- シーン変更関連 --//
  //シーンをセット
  public function SetScene($scene) {
    $this->scene = $scene;
  }

  //シーンをずらす (主に仮想処理用)
  public function ShiftScene($unshift = false) {
    if ($unshift) {
      $this->date--;
      $this->SetScene(RoomScene::NIGHT);
    } else {
      $this->date++;
      $this->SetScene(RoomScene::DAY);
    }
  }

  //夜にする
  public function ChangeNight() {
    $this->SetScene(RoomScene::NIGHT);
    if ($this->IsTest()) return true;

    RoomDB::UpdateScene();
    return $this->Talk('', TalkAction::NIGHT); //夜がきた通知
  }

  //次の日にする
  public function ChangeDate() {
    $this->ShiftScene();
    if ($this->IsTest()) return true;

    RoomDB::UpdateScene(true);
    $this->Talk($this->date, TalkAction::MORNING); //夜が明けた通知
    RoomDB::UpdateTime(); //最終書き込みを更新
    return Winner::Check(); //勝敗のチェック
  }

  //夜を飛ばす
  public function SkipNight() {
    if ($this->IsEvent('skip_night')) {
      VoteNight::Aggregate(true);
      $this->talk(TalkMessage::SKIP_NIGHT);
    }
  }

  //ゲーム開始
  public function Start() {
    $this->date++;
    $this->SetScene($this->IsOption('open_day') ? RoomScene::DAY : RoomScene::NIGHT);
    DB::$USER->GameStart($this->IsOption('limit_talk') || $this->IsOption('no_silence'));
    if (! $this->IsTest()) {
      RoomDB::Start();
      //JinrouRSS::Update(); //RSS機能はテスト中
    }

    //闇鍋配役隠蔽判定
    if ($this->IsOptionGroup('chaos') && ! $this->IsOptionGroup('chaos_open_cast')) {
      $str = TalkMessage::CHAOS;
    } else {
      $str = Cast::GenerateMessage(Cast::Stack()->Get('role_count'));
    }
    $this->Talk($str);
    if ($this->IsOption('detective')) $this->SetDetective();

    if (! $this->IsTest()) {
      RoomDB::UpdateTime(); //最終書き込み時刻を更新
      Winner::Check(); //配役時に勝敗が決定している可能性があるので勝敗判定を行う
    }
  }

  //探偵指名
  private function SetDetective() {
    //Cast::Stack()->p('detective', '◆detective');
    $stack = Cast::Stack()->Get('detective');
    Cast::Stack()->Clear('detective');
    if (1 > count($stack)) return;

    $user = Lottery::Get($stack);
    $this->Talk(sprintf(TalkMessage::DETECTIVE, $user->handle_name));

    //霊界探偵モードなら探偵を霊界に送る
    if ($this->IsOption('gm_login') && $this->IsOption('not_open_cast') &&
	Cast::Stack()->Get('user_count') > 7) {
      $user->ToDead();
    }
  }

  //-- 表示関連 --//
  //背景設定 CSS タグを生成
  public function GenerateCSS() {
    if (isset($this->scene)) return HTML::LoadCSS(sprintf('%s/game_%s', JINROU_CSS, $this->scene));
  }

  //村のタイトルタグを生成
  public function GenerateTitleTag($log = false) {
    $format = '<%s class="room"><span class="room-name">%s</span> [%s]' . Text::BR .
      '<span class="room-comment">%s</span></%s>' . Text::LF;
    $tag = $log ? 'span' : 'td';

    return sprintf($format, $tag, $this->GenerateName(), $this->GenerateNumber(),
		   $this->GenerateComment(), $tag);
  }

  //村名を生成
  public function GenerateName() {
    return $this->name . GameMessage::ROOM_TITLE_FOOTER;
  }

  //番地を生成
  public function GenerateNumber() {
    return $this->id . GameMessage::ROOM_NUMBER_FOOTER;
  }

  //村のコメントを生成
  public function GenerateComment() {
    return GameMessage::ROOM_COMMENT_HEADER . $this->comment . GameMessage::ROOM_COMMENT_FOOTER;
  }
}

//-- DB アクセス (Room 拡張) --//
class RoomDB {
  //-- room --//
  //基本取得処理
  static function Get($column, $lock = false) {
    self::Prepare(self::SetQuery($column) . ($lock ? ' FOR UPDATE' : ''));
    return DB::FetchResult();
  }

  //経過時間取得
  static function GetTime() {
    return self::Get('UNIX_TIMESTAMP() - last_update_time');
  }

  //ゲームオプション取得
  static function GetOption() {
    self::Prepare(self::SetQuery('game_option, option_role, max_user'));
    return DB::FetchAssoc(true);
  }

  //超過警告メッセージ出力済み判定
  static function IsOvertimeAlert() {
    self::Prepare(self::SetQuery('overtime_alert') . ' AND overtime_alert IS FALSE');
    return ! DB::Exists();
  }

  //最終更新時刻更新
  static function UpdateTime() {
    if (DB::$ROOM->IsTest()) return true;
    self::Prepare('UPDATE room SET last_update_time = UNIX_TIMESTAMP() WHERE room_no = ?');
    return DB::FetchBool();
  }

  //投票回数更新
  static function UpdateVoteCount($revote = false) {
    if (DB::$ROOM->IsTest()) return true;
    $format = <<<EOF
UPDATE room SET vote_count = vote_count + 1, overtime_alert = FALSE, %s WHERE room_no = ?
EOF;
    if ($revote) {
      $data = 'revote_count = revote_count + 1';
    } else {
      $data = 'last_update_time = UNIX_TIMESTAMP()';
    }

    self::Prepare(sprintf($format, $data));
    return DB::FetchBool();
  }

  //超過警告メッセージ判定フラグ変更
  static function UpdateOvertimeAlert($bool = false) {
    if (DB::$ROOM->IsTest()) return true;
    $format = <<<EOF
UPDATE room SET overtime_alert = %s, last_update_time = UNIX_TIMESTAMP() WHERE room_no = ?
EOF;
    self::Prepare(sprintf($format, $bool ? 'TRUE' : 'FALSE'));
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
    $list = array(RoomStatus::PLAYING, DB::$ROOM->date, DB::$ROOM->scene, 1, DB::$ROOM->id);
    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  //村終了処理
  static function Finish($winner) {
    $query = <<<EOF
UPDATE room SET status = ?, scene = ?, winner = ?,
scene_start_time = UNIX_TIMESTAMP(), finish_datetime = NOW()
WHERE room_no = ?
EOF;
    DB::Prepare($query, array(RoomStatus::FINISHED, RoomScene::AFTER, $winner, DB::$ROOM->id));
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
      $result->user_list[$user_no][]     = $id;
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
    case RoomScene::BEFORE:
    case RoomScene::NIGHT:
      $data = 'user_no, target_no, type';
      break;

    case RoomScene::DAY: //必要に応じて revote_count を WHERE に足す (不要のはず)
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
    if (DB::$ROOM->IsTest()) return true;
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
    return DB::Execute() && DB::Optimize('vote');
  }

  //基本 SQL セット
  private static function SetQuery($column) {
    return sprintf('SELECT %s FROM room WHERE room_no = ?', $column);
  }

  //Prepare 処理 (RoomDB 用)
  private static function Prepare($query) {
    DB::Prepare($query, array(DB::$ROOM->id));
  }
}

//-- DB アクセス (システムメッセージ系拡張) --//
class SystemMessageDB {
  //イベント情報取得
  static function GetEvent() {
    if (DB::$ROOM->IsTest()) return DevRoom::GetEvent();
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
    if (DB::$ROOM->IsTest()) return RQ::GetTest()->result_dead;
    $query = <<<EOF
SELECT date, type, handle_name, result FROM result_dead
WHERE room_no = ? AND date = ? AND scene = ?
EOF;
    $list = array(DB::$ROOM->id);
    if ($shift) {
      array_push($list, DB::$ROOM->date - 1, DB::$ROOM->scene);
    } elseif (DB::$ROOM->IsDay()) {
      array_push($list, DB::$ROOM->date - 1, RoomScene::NIGHT);
    } else {
      array_push($list, DB::$ROOM->date, RoomScene::DAY);
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
    $select = 'SELECT room_no';
    $from   = 'FROM room';
    $where  = 'WHERE status = ?';
    $order  = 'GROUP BY room_no ORDER BY room_no ' . ($reverse ? 'DESC' : 'ASC');
    $list   = array(RoomStatus::FINISHED);

    if (isset(RQ::Get()->role) || isset(RQ::Get()->name)) {
      $from  .= ' INNER JOIN user_entry USING (room_no)';
    }

    if (isset(RQ::Get()->role)) {
      $where .= ' AND role LIKE ?';
      $list[] = self::SetLike(RQ::Get()->role);
    }

    if (isset(RQ::Get()->name)) {
      $where .= ' AND (uname LIKE ? OR handle_name LIKE ?)';
      $name = self::SetLike(RQ::Get()->name);
      array_push($list, $name, $name);
    }

    if (isset(RQ::Get()->room_name)) {
      $where .= ' AND name LIKE ?';
      $list[] = self::SetLike(RQ::Get()->room_name);
    }

    $query = sprintf('%s %s %s %s', $select, $from, $where, $order);
    if (RQ::Get()->page != 'all') {
      $view = OldLogConfig::VIEW;
      $query .= sprintf(' LIMIT %d, %d', $view * (RQ::Get()->page - 1), $view);
    }

    DB::Prepare($query, $list);
    return DB::FetchColumn();
  }

  //終了した村数を取得
  static function GetFinishedCount() {
    $select = 'SELECT room_no';
    $from   = 'FROM room';
    $where  = 'WHERE status = ?';
    $list   = array(RoomStatus::FINISHED);

    if (isset(RQ::Get()->role) || isset(RQ::Get()->name)) {
      $from  .= ' INNER JOIN user_entry USING (room_no)';
    }

    if (isset(RQ::Get()->role)) {
      $where .= ' AND role LIKE ?';
      $list[] = self::SetLike(RQ::Get()->role);
    }

    if (isset(RQ::Get()->name)) {
      $where .= ' AND (uname LIKE ? OR handle_name LIKE ?)';
      $name = self::SetLike(RQ::Get()->name);
      array_push($list, $name, $name);
    }

    if (isset(RQ::Get()->room_name)) {
      $where .= ' AND name LIKE ?';
      $list[] = self::SetLike(RQ::Get()->room_name);
    }

    $query = sprintf('%s %s %s', $select, $from, $where);
    DB::Prepare($query, $list);
    return DB::Count();
  }

  //村クラス取得 (進行中)
  static function LoadOpening() {
    $query = <<<EOF
SELECT room_no AS id, name, comment, game_option, option_role, max_user, status
FROM room WHERE status <> ? ORDER BY room_no DESC
EOF;
    DB::Prepare($query, array(RoomStatus::FINISHED));
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
  (SELECT COUNT(user_no) FROM user_entry AS u
   WHERE u.room_no = r.room_no AND u.user_no > 0) AS user_count
FROM room AS r WHERE room_no = ? AND status = ?
EOF;
    DB::Prepare($query, array($room_no, RoomStatus::FINISHED));
    return DB::FetchClass('Room', true);
  }

  //村クラス取得 (ユーザ登録用)
  static function LoadEntryUser($room_no) {
    $query = <<<EOF
SELECT room_no AS id, date, scene, status, game_option, option_role, max_user
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
    return DB::Exists();
  }

  //LIKE 文セット
  private static function SetLike($str) {
    return '%' . $str . '%';
  }
}
