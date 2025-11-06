<?php
//-- 個別村クラス --//
final class Room extends StackManager {
   public $game_option;
   public $option_role;
   public $scene;

  //-- 初期化・基本関数 --//
  public function __construct($request = null, $lock = false) {
    if (null === $request) {
      return;
    }

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
    if (false === isset($this->winner)) { //未設定ならキャッシュする
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
    if (RQ::Fetch()->IsVirtualRoom()) {
      $option_role = RQ::GetTest()->test_room['option_role'];
    } else {
      $option_role = RoomDB::Get('option_role');
    }
    $this->option_role = new OptionParser($option_role);
    ArrayFilter::AddMerge($this->option_list, array_keys($this->option_role->list));
  }

  //ゲームオプションの展開処理
  public function ParseOption($join = false) {
    $this->game_option = new OptionParser($this->game_option);
    $this->option_role = new OptionParser($this->option_role);
    $this->option_list = (true === $join) ?
      array_merge(array_keys($this->game_option->list), array_keys($this->option_role->list)) :
      array_keys($this->game_option->list);

    if ($this->IsRealTime()) {
      $this->real_time = new stdClass();
      $this->real_time->day   = $this->game_option->list['real_time'][0];
      $this->real_time->night = $this->game_option->list['real_time'][1];
    }
  }

  //闇鍋モード用オプション配役データ取得
  public function GetChaosOptionList($option) {
    if ($this->IsOption($option)) {
      return ChaosConfig::${$option . '_list'}[$this->option_role->list[$option][0]];
    } else {
      return [];
    }
  }

  //決闘村用オプション配役データ取得
  public function GetDuelOptionList($option) {
    if ($this->IsOption($option)) {
      return DuelConfig::$cast_list[$this->option_role->list[$option][0]];
    } else {
      return [];
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

  //-- イベント関連 --//
  //イベント情報を DB から取得
  public function LoadEvent() {
    if (false === $this->IsPlaying()) {
      return null;
    }

    $this->Stack()->Set('event', new FlagStack());
    $this->Stack()->Set('event_row', SystemMessageDB::GetEvent());
  }

  //天候判定用の情報を DB から取得
  private function LoadWeather($shift = false) {
    if (false === $this->IsPlaying()) {
      return null;
    }

    $date = $this->date;
    if ((true === $shift && RQ::Fetch()->reverse_log) || $this->IsAfterGame()) {
      $date++;
    }

    $result = SystemMessageDB::GetWeather($date);
    if (false !== $result) {
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
    if (false === $this->IsPlaying()) {
      return [];
    }

    if (true === $force || $this->Stack()->IsEmpty('event_row')) {
      $this->LoadEvent();
    }
    return $this->Stack()->Get('event_row');
  }

  //イベント判定
  public function IsEvent($type) {
    $this->InitEvent();
    return true === $this->Stack()->Get('event')->Get($type);
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

  //-- 日付判定関連 --//
  //当日判定
  public function IsDate($date) {
    return $date == $this->date;
  }

  //日付セット (ログ用)
  public function SetDate($date) {
    return $this->date = $date;
  }

  //最終日セット (ログ用)
  /*
    日付を任意に上書きしてログを出力する際に事前に最終日を退避させておく
    現在の日付を更新する前に実行すること
   */
  public function SetLastDate() {
    return $this->last_date = $this->date;
  }

  //-- シーン判定関連 --//
  //ゲーム開始前シーン判定
  public function IsBeforeGame() {
    return RoomScene::BEFORE == $this->scene;
  }

  //ゲーム中 (昼) シーン判定
  public function IsDay() {
    return RoomScene::DAY == $this->scene;
  }

  //ゲーム中 (夜) シーン判定
  public function IsNight() {
    return RoomScene::NIGHT == $this->scene;
  }

  //ゲーム終了後シーン判定
  public function IsAfterGame() {
    return RoomScene::AFTER == $this->scene;
  }

  //-- ステータス判定関連 --//
  //ゲーム開始前判定
  public function IsWaiting() {
    return RoomStatus::WAITING == $this->status;
  }

  //募集停止中判定
  public function IsClosing() {
    return RoomStatus::CLOSING == $this->status;
  }

  //ゲーム中判定 (仮想処理をする為、status では判定しない)
  public function IsPlaying() {
    return $this->IsDay() || $this->IsNight();
  }

  //ゲーム終了判定
  public function IsFinished() {
    return RoomStatus::FINISHED == $this->status;
  }

  //ステータスセット (ログ用)
  public function SetStatus($status) {
    $this->status = $status;
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
      $this->Flag()->Set($data, OptionManager::IsRoomOpenCast());
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
  public function Talk(TalkStruct $talk) {
    if ($this->IsTest()) {
      $stack = $talk->GetStruct();
      $str = sprintf('★Talk: %s: %s: %s: %s: %s',
	$stack[TalkStruct::UNAME],
	$stack[TalkStruct::SCENE],
	$stack[TalkStruct::LOCATION],
	$stack[TalkStruct::ACTION],
	$stack[TalkStruct::FONT_TYPE]
      );
      Text::p(Text::ConvertLine($stack[TalkStruct::SENTENCE]), $str);
      return true;
    }
    return RoomTalkDB::Insert($talk);
  }

  //発言登録 (ゲーム開始前専用)
  public function TalkBeforeGame(RoomTalkBeforeGameStruct $talk) {
    if ($this->IsTest()) {
      $stack = $talk->GetStruct();
      $str = sprintf('★Talk: %s: %s: %s: %s',
	$stack[RoomTalkBeforeGameStruct::UNAME],
	$stack[RoomTalkBeforeGameStruct::HANDLE_NAME],
	$stack[RoomTalkBeforeGameStruct::COLOR],
	$stack[RoomTalkBeforeGameStruct::FONT_TYPE]
      );
      Text::p(Text::ConvertLine($stack[RoomTalkBeforeGameStruct::SENTENCE]), $str);
      return true;
    }
    return RoomTalkDB::InsertBeforeGame($talk);
  }

  //-- 時間関連 --//
  //現在時刻セット
  public function SetTime() {
    $this->system_time = Time::Get();
  }

  //突然死タイマー初期化
  public function InitializeSuddenDeath() {
    $this->sudden_death = 0;
  }

  //突然死タイマーセット
  public function SetSuddenDeath() {
    $this->sudden_death = TimeConfig::SUDDEN_DEATH - RoomDB::GetTime();
  }

  //突然死タイマーリセット
  public function ResetSuddenDeath() {
    $this->sudden_death = TimeConfig::SUDDEN_DEATH;
  }

  //超過警告メッセージ登録
  public function OvertimeAlert($str) {
    if (RoomDB::IsOvertimeAlert()) {
      return true;
    }

    RoomTalk::StoreSystem($str);
    return RoomDB::UpdateOvertimeAlert(true);
  }

  //-- 投票関連 --//
  //シーンに合わせた投票情報を DB から取得
  public function LoadVote($kick = false) {
    if (RQ::Fetch()->IsVirtualRoom()) {
      $vote_list = RQ::GetTest()->vote->{$this->scene};
      if (null === $vote_list) {
	return null;
      }
    } else {
      $vote_list = RoomDB::GetVote();
    }
    //Text::p($vote_list, '◆vote_list');

    $stack = [];
    switch ($this->scene) {
    case RoomScene::BEFORE:
      $type = (true === $kick) ? VoteAction::KICK : VoteAction::GAME_START;
      foreach ($vote_list as $list) {
	if ($list['type'] != $type) {
	  continue;
	}

	if (true === $kick) {
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
    $stack = [];
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

  //再投票カウントリセット
  public function ResetRevoteCount() {
    $this->revote_count = 0;
  }

  //-- システムメッセージ関連 --//
  //イベント情報登録
  public function StoreEvent($str, $type, $add_date = 0) {
    $date = $this->date + $add_date;
    if ($this->IsTest()) {
      Text::p("{$type} ({$date}): {$str}", '★SystemMessage');
      if (is_array(RQ::GetTest()->system_message)) {
	RQ::GetTest()->system_message[$date][$type][] = $str;
      }
      return true;
    }

    $list = [
      'room_no' => $this->id,
      'date'    => $date,
      'type'    => $type,
      'message' => $str
    ];

    return DB::Insert('system_message', $list);
  }

  //死亡情報登録
  public function StoreDead($name, $type, $result = null) {
    $date = $this->date;
    if ($this->IsTest()) {
      Text::p("{$name}: {$type} ({$date}): {$result}", '★ResultDead');
      if (is_array(RQ::GetTest()->result_dead)) {
	$stack = ['type' => $type, 'handle_name' => $name, 'result' => $result];
	RQ::GetTest()->result_dead[] = $stack;
      }
      return true;
    }

    $list = [
      'room_no' => $this->id,
      'date'    => $date,
      'scene'   => $this->scene,
      'type'    => $type
    ];
    if (isset($name)) {
      $list['handle_name'] = $name;
    }
    if (isset($result)) {
      $list['result'] = $result;
    }

    return DB::Insert('result_dead', $list);
  }

  //能力発動結果登録
  public function StoreAbility($type, $result, $target = null, $user_no = null) {
    if (OptionManager::IsSealMessage($type)) {
      if ($this->IsTest()) {
	Text::p("{$type}: {$result}: {$target}: {$user_no}", '★SealMessage');
      }
      return true;
    }

    $date = $this->date;
    if ($this->IsTest()) {
      Text::p("{$type}: {$result}: {$target}: {$user_no}", '★ResultAbility');
      if (is_array(RQ::GetTest()->result_ability)) {
	$stack = ['user_no' => $user_no, 'target' => $target, 'result' => $result];
	RQ::GetTest()->result_ability[$date][$type][] = $stack;
      }
      return true;
    }

    $list = [
      'room_no' => $this->id,
      'date'    => $date,
      'type'    => $type
    ];
    foreach (['result', 'target', 'user_no'] as $data) {
      if (isset($$data)) {
	$list[$data] = $$data;
      }
    }

    return DB::Insert('result_ability', $list);
  }

  //天候登録
  public function StoreWeather($id, $date, $priest = false) {
    $this->StoreEvent($id, EventType::WEATHER, $date);
    if (true === $priest) { //祈祷師の処理
      $result = 'prediction_weather_' . WeatherManager::GetEvent($id);
      $this->StoreAbility(RoleAbility::WEATHER_PRIEST, $result);
    }
  }

  //-- シーン変更関連 --//
  //シーンをセット
  public function SetScene($scene) {
    $this->scene = $scene;
  }

  //シーンをずらす (主に仮想処理用)
  public function ShiftScene($unshift = false) {
    if (true === $unshift) {
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
    if ($this->IsTest()) {
      return true;
    }

    RoomDB::UpdateScene();
    $talk = new RoomTalkStruct('');
    $talk->Set(TalkStruct::ACTION, TalkAction::NIGHT); //夜がきた通知
    return $this->Talk($talk);
  }

  //次の日にする
  public function ChangeDate() {
    $this->ShiftScene();
    if ($this->IsTest()) {
      return true;
    }

    RoomDB::UpdateScene(true);
    $talk = new RoomTalkStruct($this->date);
    $talk->Set(TalkStruct::ACTION, TalkAction::MORNING); //夜が明けた通知
    $this->Talk($talk);
    RoomDB::UpdateTime(); //最終書き込みを更新
    return Winner::Judge(); //勝敗判定
  }

  //夜を飛ばす
  public function SkipNight() {
    if ($this->IsEvent('skip_night')) {
      VoteNight::Aggregate(true);
      RoomTalk::StoreSystem(TalkMessage::SKIP_NIGHT);
    }
  }

  //ゲーム開始
  public function Start() {
    $this->date++;
    $this->SetScene(OptionManager::GetRoomGameStartScene());
    DB::$USER->GameStart();
    if (false === $this->IsTest()) {
      RoomDB::Start();
    }

    //配役一覧登録
    RoomTalk::StoreSystem(Cast::GenerateMessage(Cast::Stack()->Get(Cast::SUM)));
    if ($this->IsOption('detective')) { //探偵指名
      OptionLoader::Load('detective')->Designate();
    }

    if (false === $this->IsTest()) {
      RoomDB::UpdateTime(); //最終書き込み時刻を更新
      Winner::Judge(); //配役時に勝敗が決定している可能性があるので勝敗判定を行う
    }
  }

  //-- 表示関連 --//
  //背景設定 CSS 出力
  public function OutputCSS() {
    if (isset($this->scene)) {
      HTML::OutputCSS(sprintf('%s/game_%s', JINROU_CSS, $this->scene));
    }
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
}

//-- 発言処理クラス (Room 拡張) --//
final class RoomTalk {
  //システムメッセージ登録
  public static function StoreSystem($sentence) {
    DB::$ROOM->Talk(new RoomTalkStruct($sentence));
  }

  //BeforeGame 専用メッセージ登録
  public static function StoreBeforeGame($sentence, User $user, $font_type = null) {
    DB::$ROOM->TalkBeforeGame(new RoomTalkBeforeGameStruct($sentence, $user, $font_type));
  }
}

//-- Talk 構造体基底クラス --//
abstract class TalkStruct extends StructBase {
  const SCENE      = 'scene';
  const LOCATION   = 'location';
  const UNAME      = 'uname';
  const ROLE_ID    = 'role_id';
  const ACTION     = 'action';
  const SENTENCE   = 'sentence';
  const FONT_TYPE  = 'font_type';
  const SPEND_TIME = 'spend_time';
}

//-- Room 用 Talk 構造体 --//
final class RoomTalkStruct extends TalkStruct {
  //コンストラクタ
  public function __construct($sentence) {
    $this->struct = [
      self::SCENE      => DB::$ROOM->scene,
      self::LOCATION   => TalkLocation::SYSTEM,
      self::UNAME      => GM::SYSTEM,
      self::ROLE_ID    => null,
      self::ACTION     => null,
      self::SENTENCE   => $sentence,
      self::FONT_TYPE  => null,
      self::SPEND_TIME => 0
    ];
  }
}

//-- Room 用 Talk 構造体 (BeforeGame 専用) --//
final class RoomTalkBeforeGameStruct extends TalkStruct {
  const HANDLE_NAME = 'handle_name';
  const COLOR       = 'color';

  //コンストラクタ
  public function __construct($sentence, User $user, $font_type = null) {
    $this->struct = [
      self::UNAME       => $user->uname,
      self::HANDLE_NAME => $user->handle_name,
      self::COLOR       => $user->color,
      self::SENTENCE    => $sentence,
      self::FONT_TYPE   => $font_type
    ];
  }
}
