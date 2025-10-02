<?php
//-- 個別村クラス --//
class Room extends StackManager {
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

  //-- 初期化・基本関数 --//
  public function __construct($request = null, $lock = false) {
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
    $stack = RoomLoaderDB::Get($room_no, $lock);
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
    ArrayFilter::Merge($this->option_list, array_keys($this->option_role->list));
  }

  //ゲームオプションの展開処理
  public function ParseOption($join = false) {
    $this->game_option = new OptionParser($this->game_option);
    $this->option_role = new OptionParser($this->option_role);
    $this->option_list = $join ?
      array_merge(array_keys($this->game_option->list), array_keys($this->option_role->list)) :
      array_keys($this->game_option->list);

    if ($this->IsRealTime()) {
      $this->real_time = new stdClass();
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
      if (Text::Search($option, $target_option)) {
	return true;
      }
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
    if (($shift && RQ::Get()->reverse_log) || $this->IsAfterGame()) {
      $date++;
    }

    $result = SystemMessageDB::GetWeather($date);
    if ($result !== false) {
      $this->Stack()->Set('weather', $result); //天候を格納
    } else {
      $this->Stack()->Clear('weather'); //ログ用に初期化する
    }
  }

  //イベント情報初期化
  public function InitEvent() {
    if ($this->Stack()->IsEmpty('event')) {
      $this->Stack()->Set('event', new FlagStack());
    }
  }

  //イベント情報消去
  public function ResetEvent() {
    $this->Stack()->Clear('event');
    $this->Stack()->Clear('event_row');
  }

  //イベント情報取得
  public function GetEvent($force = false) {
    if (! $this->IsPlaying()) return array();

    if ($force || $this->Stack()->IsEmpty('event_row')) {
      $this->LoadEvent();
    }
    return $this->Stack()->Get('event_row');
  }

  //イベント判定
  public function IsEvent($type) {
    $this->InitEvent();
    return $this->Stack()->Get('event')->Get($type);
  }

  //天候セット (ログ用)
  public function SetWeather() {
    if ($this->IsOn(RoomMode::WATCH) || $this->IsOn(RoomMode::SINGLE)) {
      $this->LoadWeather();
      $stack = $this->Stack();
      if ($stack->Exists('weather') && WeatherManager::Exists($stack->Get('weather'))) {
	$this->InitEvent();
	$stack->Get('event')->On(WeatherManager::GetEvent($stack->Get('weather')));
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

  //募集停止中判定
  public function IsClosing() {
    return $this->status == RoomStatus::CLOSING;
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

  //-- モード判定関連 --//
  //テストモード判定
  public function IsTest() {
    return $this->IsOn(RoomMode::TEST);
  }

  //-- 情報公開判定関連 --//
  //霊界公開判定
  public function IsOpenCast() {
    $data = 'open_cast';
    if ($this->Flag()->IsEmpty($data)) { //未設定ならキャッシュする
      if ($this->IsOption('not_open_cast')) { //常時非公開
	$user = DB::$USER->ByID(GM::ID); //身代わり君の蘇生辞退判定
	$flag = $user->IsDummyBoy() && $user->IsDrop() && DB::$USER->IsOpenCast();
      } elseif ($this->IsOption('auto_open_cast')) { //自動公開
	$flag = DB::$USER->IsOpenCast();
      } else { //常時公開
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
      (DB::$SELF->IsDead() && $this->IsOff(RoomMode::SINGLE) && $this->IsOpenCast()) ||
      ($virtual ? $this->IsAfterGame() : ($this->IsFinished() && $this->IsOff(RoomMode::SINGLE)));
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
    if ($uname == '') {
      $uname = GM::SYSTEM;
    }
    if ($scene == '') {
      $scene = $this->scene;
      if (is_null($location)) {
	$location = TalkLocation::SYSTEM;
      }
    }
    if ($this->IsTest()) {
      $str = sprintf('★Talk: %s: %s: %s: %s: %s', $uname, $scene, $location, $action, $font_type);
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
      $str = sprintf('★Talk: %s: %s: %s: %s', $uname, $handle_name, $color, $font_type);
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
      $type = $kick ? VoteAction::KICK : VoteAction::GAME_START;
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
      Text::p("{$type} ({$date}): {$str}", '★SystemMessage');
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
      Text::p("{$name}: {$type} ({$date}): {$result}", '★ResultDead');
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
      Text::p("{$type}: {$result}: {$target}: {$user_no}", '★ResultAbility');
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
    $this->SystemMessage($id, EventType::WEATHER, $date);
    if ($priest) { //祈祷師の処理
      $result = 'prediction_weather_' . WeatherManager::GetEvent($id);
      $this->ResultAbility(RoleAbility::WEATHER_PRIEST, $result);
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
    }

    //闇鍋配役隠蔽判定
    if ($this->IsOptionGroup('chaos') && ! $this->IsOptionGroup('chaos_open_cast')) {
      $str = TalkMessage::CHAOS;
    } else {
      $str = Cast::GenerateMessage(Cast::Stack()->Get(Cast::SUM));
    }
    $this->Talk($str);
    if ($this->IsOption('detective')) OptionLoader::Load('detective')->Designate(); //探偵指名

    if (! $this->IsTest()) {
      RoomDB::UpdateTime(); //最終書き込み時刻を更新
      Winner::Check(); //配役時に勝敗が決定している可能性があるので勝敗判定を行う
    }
  }

  //-- 表示関連 --//
  //背景設定 CSS 生成
  public function GenerateCSS() {
    if (isset($this->scene)) return HTML::LoadCSS(sprintf('%s/game_%s', JINROU_CSS, $this->scene));
  }

  //タイトル生成
  public function GenerateTitle($log = false) {
    $tag = $log ? 'span' : 'td';
    return Text::Format($this->GetTitle(),
      $tag, $this->GenerateName(), $this->GenerateNumber(), Text::BR,
      $this->GenerateComment(), $tag
    );
  }

  //村名生成
  public function GenerateName() {
    return $this->name . GameMessage::ROOM_TITLE_FOOTER;
  }

  //番地生成
  public function GenerateNumber() {
    return $this->id . GameMessage::ROOM_NUMBER_FOOTER;
  }

  //コメント生成
  public function GenerateComment() {
    return GameMessage::ROOM_COMMENT_HEADER . $this->comment . GameMessage::ROOM_COMMENT_FOOTER;
  }

  //タイトルタグ
  private function GetTitle() {
    return <<<EOF
<%s class="room"><span class="room-name">%s</span> [%s]%s<span class="room-comment">%s</span></%s>
EOF;
  }
}
