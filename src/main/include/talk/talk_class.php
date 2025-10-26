<?php
//-- 発言処理クラス --//
final class Talk extends StackStaticManager {
  /* フラグ */
  const UPDATE		= 'update';	//キャッシュ更新
  const LIMIT_SAY	= 'limit_say';	//発言制限
  const LIMIT_TALK	= 'limit_talk';	//発言数制限

  /* 内部格納クラス */
  private static $instance = null; //TalkBuilder クラス

  //会話取得
  public static function Fetch() {
    $builder = new TalkBuilder('talk');
    foreach (TalkDB::Get() as $talk) {
      $builder->Generate($talk);
    }
    $builder->GenerateTimeStamp();
    return $builder;
  }

  //会話取得 (霊界用)
  public static function FetchHeaven() {
    //出力条件をチェック
    //if (DB::$SELF->IsDead()) return false; //呼び出し側でチェックするので現在は不要

    $builder = new TalkBuilder('talk');
    $builder->flag->open_cast = DB::$ROOM->IsOpenCast(); //霊界公開判定
    foreach (TalkDB::Get(true) as $talk) {
      $builder->GenerateHeaven($talk);
    }
    return $builder;
  }

  //会話出力
  public static function Output() {
    return self::Fetch()->Output();
  }

  //会話出力 (霊界用)
  public static function OutputHeaven() {
    return self::FetchHeaven()->Output();
  }

  //TalkBuilder クラス登録
  public static function SetBuilder(TalkBuilder $builder) {
    self::$instance = $builder;
  }

  //TalkBuilder クラス取得
  public static function GetBuilder() {
    return self::$instance;
  }
}

//-- 発言パーサ --//
final class TalkParser extends stdClass {
  public $uname;
  public $action;
  public $location;

  public function __construct($list = null) {
    if (is_array($list)) {
      foreach ($list as $key => $data) {
	$this->$key = $data;
      }
    }
    if (isset($this->time)) {
      $this->date_time = Time::GetTimeStamp($this->time);
    }
    $this->Parse();
  }

  //データ解析
  private function Parse() {
    switch ($this->uname) {
    case GM::SYSTEM:
      switch ($this->action) {
      case TalkAction::MORNING:
	$this->sentence = sprintf(TalkMessage::MORNING, $this->sentence);
	return;

      case TalkAction::NIGHT:
	$this->sentence = TalkMessage::NIGHT;
	return;
      }
      return;

    default:
      if ($this->location == TalkLocation::SYSTEM) {
	$this->ParseSystem();
      }
      return;
    }
  }

  //投票データ解析
  private function ParseSystem() {
    $action = $this->action;
    switch ($this->action) { //大文字小文字をきちんと区別してマッチングする
    /* メッセージ固定型 */
    case TalkAction::OBJECTION:
      $this->sex      = $this->sentence;
      $this->sentence = Objection::GetTalk($this->sex);
      return;

    case VoteAction::EXIT_DO:
    case VoteAction::NOT_EXIT:
      $this->class    = VoteCSS::ESCAPE;
      $this->sentence = VoteTalkMessage::${$this->action};
      return;

    /* キャンセル型 */
    case VoteAction::NOT_REVIVE:
      $this->class     = VoteCSS::REVIVE;
      $this->sentence .= VoteTalkMessage::$REVIVE_NOT_DO;
      return;

    case VoteAction::NOT_ASSASSIN:
      $this->class     = VoteCSS::ASSASSIN;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case VoteAction::NOT_STEP:
      $this->class     = VoteCSS::STEP;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case VoteAction::NOT_POSSESSED:
      $this->class     = VoteCSS::WOLF;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case VoteAction::NOT_GRAVE:
      $this->class     = VoteCSS::WOLF;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case VoteAction::NOT_TRAP:
      $this->class     = VoteCSS::WOLF;
      $this->sentence .= VoteTalkMessage::$TRAP_NOT_DO;
      return;

    case VoteAction::NOT_OGRE:
      $this->class     = VoteCSS::OGRE;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case VoteAction::NOT_DEATH_NOTE:
      $this->class     = VoteCSS::DEATH_NOTE;
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    /* action, class 入れ替え型 */
    case VoteAction::STEP_MAGE:
    case VoteAction::CHILD_FOX:
      $action      = VoteAction::MAGE;
      $this->class = VoteCSS::MAGE;
      break;

    case VoteAction::STEP_GUARD:
      $action      = VoteAction::GUARD;
      $this->class = VoteCSS::GUARD;
      break;

    case VoteAction::REVIVE:
      $action      = 'REVIVE_DO';
      $this->class = VoteCSS::REVIVE;
      break;

    case VoteAction::STEP_ASSASSIN:
      $action      = VoteAction::ASSASSIN;
      $this->class = VoteCSS::ASSASSIN;
      break;

    case VoteAction::STEP_SCAN:
      $action      = VoteAction::SCAN;
      $this->class = VoteCSS::SCAN;
      break;

    case VoteAction::PLURAL_WIZARD:
    case VoteAction::SPREAD_WIZARD:
      $action      = VoteAction::WIZARD;
      $this->class = VoteCSS::WIZARD;
      break;

    case VoteAction::STEP_WOLF:
      $action      = VoteAction::WOLF;
      $this->class = VoteCSS::WOLF;
      break;

    case VoteAction::STEP_VAMPIRE:
      $action      = VoteAction::VAMPIRE;
      $this->class = VoteCSS::VAMPIRE;
      break;

    case VoteAction::JAMMER:
    case VoteAction::VOODOO_MAD:
    case VoteAction::VOODOO_FOX:
    case VoteAction::TRAP:
      $action      = Text::CutPick($action) . '_DO';
      $this->class = VoteCSS::WOLF;
      break;

    /* class 入れ替え型 */
    case VoteAction::VOODOO_KILLER:
      $this->class = VoteCSS::MAGE;
      break;

    case VoteAction::REPORTER:
    case VoteAction::ANTI_VOODOO:
      $this->class = VoteCSS::GUARD;
      break;

    case VoteAction::SILENT_WOLF:
    case VoteAction::DREAM:
    case VoteAction::POSSESSED:
    case VoteAction::GRAVE:
      $this->class = VoteCSS::WOLF;
      break;

    default:
      $this->class = strtolower(strtr($action, '_', '-'));
      break;
    }
    $this->sentence = sprintf(VoteTalkMessage::FORMAT, $this->sentence . VoteTalkMessage::$$action);
    return;
  }
}

//-- 会話生成クラス --//
final class TalkBuilder {
  public  $filter = [];
  public  $flag;
  private $actor;
  private $cache;

  public function __construct($css, $id = null) {
    $this->actor = DB::$SELF->GetVirtual(); //仮想ユーザを取得
    $this->LoadVirtualRole();
    $this->LoadFilter();
    $this->LoadFlag();
    $this->Begin($css, $id);
  }

  //テーブルヘッダ生成
  public function Begin($css, $id = null) {
    $this->cache = TalkHTML::GenerateHeader($css, $id);
  }

  //発言生成
  public function Generate(TalkParser $talk) {
    //$this->TalkDebug(print_r($talk, true), '◆talk[row]');
    //発言ユーザを取得
    /*
      $uname は必ず $talk から取得すること。
      DB::$USER にはシステムユーザー 'system' が存在しないため、$actor は常に null になっている。
      速度を取るため sprintf() を使わないこと
    */
    $actor = DB::$USER->ByUname($talk->uname);
    $real  = $actor;
    if (DB::$ROOM->IsOn(RoomMode::LOG) && isset($talk->role_id)) { //役職スイッチ処理
      //閲覧者のスイッチに伴う可視性のリロード処理
      if ($actor->ChangePlayer($talk->role_id) && $actor->IsSame($this->actor)) {
	//$this->TalkDebug($talk->role_id, '◆Switch');
	$this->LoadFilter();
	$this->LoadFlag();
      }
    }

    switch ($talk->scene) { //仮想ユーザセット
    case RoomScene::DAY:
    case RoomScene::NIGHT:
      $virtual = DB::$USER->ByVirtual($actor->id);
      if (false === $actor->IsSame($virtual)) {
	$actor = $virtual;
      }
      break;
    }

    if ($talk->uname == GM::SYSTEM) { //基本パラメータを取得
      $symbol    = '';
      $name      = '';
      $actor->id = 0;
    } else {
      $symbol = $this->GetSymbol($talk, $actor);
      $name   = $this->GetHandleName($talk, $actor);
    }

    //実ユーザを取得
    if (RQ::Get()->add_role && $actor->id > 0) { //役職表示モード対応
      $real_actor = isset($real) ? $real : $actor;
      $name .= $real_actor->GenerateShortRoleName($talk->scene == RoomScene::HEAVEN);
    } else {
      $real_actor = DB::$USER->ByRealUname($talk->uname);
    }

    switch ($talk->location) { //特殊発言
    case TalkLocation::SYSTEM:
      return $this->TalkSystem($talk, $actor, $name);

    case TalkLocation::DUMMY_BOY:
      return $this->TalkDummyBoy($talk, $real_actor);
    }

    switch ($talk->scene) { //シーン別発言
    case RoomScene::DAY:
      return $this->TalkDay($talk, $actor, $real_actor, $real);

    case RoomScene::NIGHT:
      if ($this->flag->open_talk) {
	return $this->TalkOpenNight($talk, $actor, $symbol, $name);
      } else {
	return $this->TalkNight($talk, $actor, $real_actor, $real);
      }

    case RoomScene::HEAVEN:
      return $this->TalkHeaven($talk, $symbol, $name);

    default:
      return $this->Talk($talk, $actor, $real);
    }
  }

  //発言生成 (霊界用)
  public function GenerateHeaven(TalkParser $talk) {
    $user = DB::$USER->ByUname($talk->uname); //ユーザを取得

    if ($this->flag->open_cast) {
      $handle_name = $user->handle_name . TalkHTML::GenerateInfo($talk->uname); //HN 追加処理
    } else {
      $handle_name = $user->handle_name;
    }

    $stack = [
      TalkElement::SYMBOL   => TalkHTML::GenerateSymbol($user->color),
      TalkElement::NAME     => $handle_name,
      TalkElement::VOICE    => $talk->font_type,
      TalkElement::SENTENCE => $talk->sentence
    ];
    return $this->Register($stack);
  }

  //時刻生成
  public function GenerateTimeStamp() {
    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
      $type     = 'establish_datetime'; //村立て時刻
      $sentence = TalkMessage::ESTABLISH;
      break;

    case RoomScene::DAY: //OP の昼限定
      if (false === DateBorder::One()) {
	return false;
      }
      $type     = 'start_datetime'; //ゲーム開始時刻
      $sentence = TalkMessage::GAME_START;
      break;

    case RoomScene::NIGHT:
      if (false === DateBorder::One()) {
	return false;
      }
      $type     = 'start_datetime'; //ゲーム開始時刻
      $sentence = TalkMessage::GAME_START;
      break;

    case RoomScene::AFTER:
      $type     = 'finish_datetime'; //ゲーム終了時刻
      $sentence = TalkMessage::GAME_END;
      break;

    default:
      return false;
    }

    $time = RoomDB::Get($type);
    if (null === $time) {
      return false;
    }

    $talk = new TalkParser();
    $talk->sentence   = $sentence . Time::ConvertTimeStamp($time);
    $talk->uname      = GM::SYSTEM;
    $talk->scene      = DB::$ROOM->scene;
    $talk->location   = TalkLocation::SYSTEM;
    $talk->time_stamp = Time::ConvertTimeStamp($time, false);
    return $this->Generate($talk);
  }

  //発言登録
  public function Register(array $list) {
    extract($list);
    $stack = [];
    foreach (TalkElement::$css as $key) {
      if (isset($$key) && $$key != '') {
	$$key = ' ' . $$key;
      } else {
	$$key = '';
      }
      $stack[$key] = $$key;
    }
    $sentence = Text::ConvertLine($sentence);
    $sentence = $this->QuoteTalk($sentence);

    foreach (TalkElement::$list as $key) {
      $stack[$key] = isset($$key) ? $$key : '';
    }
    $this->cache .= TalkHTML::Generate($stack);
    return true;
  }

  //発言 (デバッグ用)
  public function TalkDebug($sentence, $symbol = '') {
    $stack = [
      TalkElement::SYMBOL   => $symbol,
      TalkElement::VOICE    => null,
      TalkElement::SENTENCE => $sentence
    ];
    return $this->Register($stack);
  }

  //キャッシュリセット
  public function Refresh() {
    $str = $this->cache . TalkHTML::GenerateFooter();
    $this->cache = '';
    return $str;
  }

  //出力処理
  public function Output() {
    echo $this->Refresh();
  }

  //IDセット
  public function GetTalkID(TalkParser $talk) {
    return DB::$ROOM->IsOn(RoomMode::AUTO_PLAY) ? AutoPlayTalk::GetTalkID($talk) : '';
  }

  //仮想役職セット (本人視点が変化するタイプにセットする)
  private function LoadVirtualRole() {
    //観戦モード判定
    if (DB::$ROOM->IsFinished() || (isset($this->actor->live) && DB::$ROOM->IsOpenCast())) {
      return;
    }

    $is_day = DB::$ROOM->IsDay();
    $stack  = ['blinder' => $is_day, 'earplug' => $is_day, 'deep_sleep' => true];
    foreach ($stack as $role => $flag) {
      if ((true === $flag && DB::$ROOM->IsEvent($role)) || DB::$ROOM->IsOption($role)) {
	$this->actor->virtual_live = true;
	$this->actor->AddVirtualRole($role);
      }
    }
  }

  //役職情報ロード
  private function LoadFilter() {
    if (false === isset($this->actor->virtual_live)) {
      $this->actor->virtual_live = false;
    }
    RoleManager::Stack()->Set('viewer', $this->actor);
    RoleManager::Stack()->Set('builder', $this);
    $this->filter = RoleLoader::LoadUser($this->actor, 'talk');
  }

  //フィルタ用フラグセット
  private function LoadFlag() {
    $stack = new stdClass();

    /* 共通フラグ */
    $is_date = DateBorder::Second();

    /* 基本情報 */
    $stack->dummy_boy = DB::$SELF->IsDummyBoy();
    $stack->common    = RoleUser::IsCommon($this->actor);
    $stack->wolf      = RoleUser::IsWolf(DB::$SELF) || $this->actor->IsRole('whisper_mad');
    $stack->fox       = RoleUser::IsFox(DB::$SELF);
    $stack->lovers    = DB::$SELF->IsRole('lovers');
    $stack->open_talk = DB::$ROOM->IsOpenData();
    $stack->mind_read = $is_date && (DB::$ROOM->IsOn(RoomMode::SINGLE) || DB::$SELF->IsLive());

    if (DB::$ROOM->IsOn(RoomMode::WATCH)) {
      $stack->wolf |= RQ::Get()->wolf_sight; //狼視点モード
    }
    foreach (['common', 'wolf', 'fox'] as $type) { //身代わり君の上書き判定
      $stack->$type |= $stack->dummy_boy;
    }

    /* 耳鳴り関連 */
    //憑依追跡対応のため、SELF と actor を使い分けて判定する
    $stack->deep_sleep = $this->actor->IsRole('deep_sleep');
    $not_sleep = ! $stack->deep_sleep;

    $stack->common_whisper  = $not_sleep && RoleUser::CommonWhisper($this->actor);
    $stack->wolf_howl       = $not_sleep && RoleUser::WolfHowl($this->actor);
    $stack->whisper_ringing = $not_sleep && RoleUser::WhisperRinging($this->actor);
    $stack->howl_ringing    = $not_sleep && RoleUser::HowlRinging($this->actor);
    $stack->sweet_ringing   = $not_sleep && RoleUser::SweetRinging($this->actor) && $is_date;

    $this->flag = $stack;
  }

  //symbol 取得
  private function GetSymbol(TalkParser $talk, User $user) {
    return TalkHTML::GenerateSymbol(isset($talk->color) ? $talk->color : $user->color);
  }

  //handle_name 取得
  private function GetHandleName(TalkParser $talk, User $user) {
    return isset($talk->handle_name) ? $talk->handle_name : $user->handle_name;
  }

  //発言透過判定
  private function IsMindRead(TalkParser $talk, User $user, User $real, $secret = null) {
    $mind_read = $this->IsMindReadRole($user, $real); //特殊発言透過判定
    $location  = $secret ? RoleTalk::GetLocation($user, $real) : $talk->location;
    //$this->TalkDebug($location, '◆Location');
    switch ($location) {
    case TalkLocation::COMMON: //共有者
      return $mind_read || $this->flag->common;

    case TalkLocation::WOLF: //人狼
      return $mind_read || $this->flag->wolf;

    case TalkLocation::MAD: //囁き狂人
      return $mind_read || $this->flag->wolf;

    case TalkLocation::FOX: //妖狐
      return $mind_read || $this->flag->fox;

    case TalkLocation::MONOLOGUE: //独り言
      return $mind_read || $this->flag->dummy_boy || $this->actor->IsSameName($talk->uname);

    default:
      if (null === $location) { //ここに来たらロジックエラー
	$this->TalkDebug('Error: Location Error: ' . $location, '◆Location Check');
	return false;
      }

      //個別発言判定
      list($parse_location, $location_id) = Text::Parse($location, ':');
      if ($parse_location != TalkLocation::INDIVIDUAL) { //ここに来たらロジックエラー
	$this->TalkDebug('Error: Location Error: ' . $location, '◆Location Check');
	return false;
      }
      return $mind_read || $location_id == $this->actor->id;
    }
  }

  //発言透過役職判定
  private function IsMindReadRole(User $user, User $real) {
    foreach (RoleLoader::LoadUser($user, 'mind_read') as $filter) {
      if ($filter->IsMindRead()) {
	return true;
      }
    }

    foreach (RoleLoader::LoadUser($this->actor, 'mind_read_active') as $filter) {
      if ($filter->IsMindReadActive($user)) {
	return true;
      }
    }

    foreach (RoleLoader::LoadUser($real, 'mind_read_possessed') as $filter) {
      if ($filter->IsMindReadPossessed($user)) {
	return true;
      }
    }

    return false;
  }

  //発言
  private function Talk(TalkParser $talk, User $user, $real = null) {
    //表示情報を抽出
    $name = $this->GetHandleName($talk, $user);
    if (RQ::Get()->add_role && $user->id != 0) { //役職表示モード対応
      if ($talk->scene == RoomScene::HEAVEN) {
	$real = $user;
      } elseif (null === $real) {
	$real = DB::$USER->ByReal($user->id);
      }
      $name .= $real->GenerateShortRoleName();
    } elseif (RQ::Get()->name && DB::$ROOM->IsFinished()) { //ユーザ名表示モード
      $name .= $user->GenerateShortRoleName();
    }

    $talk->individual = false;
    if (DB::$ROOM->IsNight() &&
	(($talk->location == TalkLocation::MONOLOGUE && ! $user->IsRole('dummy_common')) ||
	 $user->IsRole('leader_common', 'mind_read', 'mind_open'))) {
      $name .= TalkHTML::GenerateSelfTalk();
    } elseif ($talk->uname == GM::DUMMY_BOY && null !== $talk->location) {
      //個別発言判定
      list($parse_location, $location_id) = Text::Parse($talk->location, ':');
      if ($parse_location == TalkLocation::INDIVIDUAL) {
	$name .= Text::BRLF . ' -> ' . DB::$USER->ByID($location_id)->GetName();
	$talk->individual = true;
      }
    }

    $voice    = $talk->font_type;
    $sentence = $talk->sentence;
    foreach ($this->filter as $filter) { //発言フィルタ処理
      $filter->FilterTalk($user, $name, $voice, $sentence);
    }

    $stack = [
      TalkElement::ID       => $this->GetTalkID($talk),
      TalkElement::SYMBOL   => $this->AddIcon($user, $this->GetSymbol($talk, $user), $name),
      TalkElement::NAME     => $name . $this->AddTimeName($talk),
      TalkElement::VOICE    => $voice,
      TalkElement::SENTENCE => $sentence
    ];
    if ($talk->location == TalkLocation::SECRET) {
      $stack[TalkElement::CSS_ROW] = TalkVoice::SECRET;
      $stack[TalkElement::SYMBOL] .= TalkMessage::SECRET_SYMBOL;
    }
    if (true === $talk->individual) {
      $stack[TalkElement::CSS_ROW] = TalkVoice::INDIVIDUAL;
    }

    return $this->Register($stack);
  }

  //発言 (システムメッセージ)
  private function TalkSystem(TalkParser $talk, User $user, $name) {
    $str = $talk->sentence . $this->AddTime($talk);
    if (false === isset($talk->action)) { //標準処理
      return $this->RegisterSystem($str, $this->GetTalkID($talk));
    }

    switch ($talk->action) { //投票情報
    case TalkAction::OBJECTION: //「異議」ありは常時表示
      $sex = empty($talk->sex) ? Sex::Get($user) : $talk->sex;
      $css = 'objection-' . $sex;
      return $this->RegisterSystemMessage($name . $str, $this->GetTalkID($talk), $css);

    case TalkAction::MORNING:
    case TalkAction::NIGHT:
      return $this->RegisterSystem($str, $this->GetTalkID($talk));

    default: //ゲーム開始前の投票 (例：KICK) は常時表示
      if ($this->flag->open_talk || DB::$ROOM->IsBeforeGame()) {
	return $this->RegisterSystemMessage($name . $str, $this->GetTalkID($talk), $talk->class);
      }
      return false;
    }
  }

  //発言 (身代わり君専用)
  private function TalkDummyBoy(TalkParser $talk, User $user) {
    $str = Message::SYMBOL . $user->handle_name . Message::SPACER . $talk->sentence;
    $str = $this->QuoteTalk($str) . $this->AddTime($talk);
    return $this->RegisterSystem($str, $this->GetTalkID($talk), TalkCSS::DUMMY);
  }

  //発言 (昼)
  private function TalkDay(TalkParser $talk, User $actor, User $real_actor, User $real) {
    if ($talk->location == TalkLocation::SECRET && ! $this->flag->open_talk) {
      if (DB::$ROOM->IsOption('secret_talk')) {
	if (! $this->IsMindRead($talk, $actor, $real_actor, true)) {
	  return false;
	}
      } elseif (! $this->actor->IsSame($actor)) {
	return false;
      }
    }

    //強風判定 (身代わり君と本人は対象外)
    if (DB::$ROOM->IsEvent('blind_talk_day') &&
	! $this->flag->dummy_boy && ! $this->actor->IsSameName($talk->uname)) {
      //位置判定 (観戦者以外の上下左右)
      $viewer = $this->actor->id;
      if ((null === $viewer) || ! Position::IsCross($actor->id, $viewer)) {
	$talk->sentence = RoleTalkMessage::COMMON_TALK;
      }
    }

    //個別発言判定
    if ($talk->uname == GM::DUMMY_BOY && null !== $talk->location && ! $this->flag->open_talk) {
      list($parse_location, $location_id) = Text::Parse($talk->location, ':');
      if ($parse_location == TalkLocation::INDIVIDUAL) {
	if ($location_id != $this->actor->id) {
	  return false;
	}
      }
    }

    return $this->Talk($talk, $actor, $real);
  }

  //発言 (夜)
  private function TalkNight(TalkParser $talk, User $actor, User $real_actor, User $real) {
    if ($this->IsMindRead($talk, $actor, $real_actor)) { //発言透過判定
      return $this->Talk($talk, $actor, $real);
    }

    switch ($talk->location) {
    case TalkLocation::COMMON: //共有者
      $filter = RoleLoader::LoadMain($actor);
      if (! method_exists($filter, 'Whisper')) { //player スイッチによる不整合対策
	return false;
      } elseif ($filter->Whisper($this, $talk)) {
	return true;
      }

      foreach (RoleLoader::LoadType('talk_whisper') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }
      return false;

    case TalkLocation::WOLF: //人狼
      $filter = RoleLoader::LoadMain($actor);
      if (! method_exists($filter, 'Howl')) { //player スイッチによる不整合対策
	return false;
      } elseif ($filter->Howl($this, $talk)) {
	return true;
      }

      foreach (RoleLoader::LoadType('talk_whisper') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }
      return false;

    case TalkLocation::MAD: //囁き狂人
      foreach (RoleLoader::LoadUser($actor, 'talk_whisper') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }
      return false;

    case TalkLocation::FOX: //妖狐
      foreach (RoleLoader::LoadUser(DB::$SELF, 'talk_fox') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }

      foreach (RoleLoader::LoadUser($actor, 'talk_whisper') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }
      return false;

    case TalkLocation::MONOLOGUE: //独り言
      foreach (RoleLoader::LoadUser($actor, 'talk_self') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }

      foreach (RoleLoader::LoadUser($this->actor, 'talk_ringing') as $filter) {
	if ($filter->Whisper($this, $talk)) {
	  return true;
	}
      }
      return false;
    }
  }

  //発言 (夜 + 公開)
  private function TalkOpenNight(TalkParser $talk, User $user, $symbol, $name) {
    $css   = '';
    $voice = $talk->font_type;
    $talk->individual = false;
    switch ($talk->location) {
    case TalkLocation::COMMON:
      $css    = TalkCSS::NIGHT_COMMON;
      $name  .= TalkHTML::GenerateInfo(TalkMessage::COMMON);
      $voice .= ' ' . $css;
      break;

    case TalkLocation::WOLF:
      $css    = TalkCSS::NIGHT_WOLF;
      $name  .= TalkHTML::GenerateInfo(TalkMessage::WOLF);
      $voice .= ' ' . $css;
      break;

    case TalkLocation::MAD:
      $css    = TalkCSS::NIGHT_WOLF;
      $name  .= TalkHTML::GenerateInfo(TalkMessage::MAD);
      $voice .= ' ' . $css;
      break;

    case TalkLocation::FOX:
      $css    = TalkCSS::NIGHT_FOX;;
      $name  .= TalkHTML::GenerateInfo(TalkMessage::FOX);
      $voice .= ' ' . $css;
      break;

    case TalkLocation::MONOLOGUE:
      $css    = TalkCSS::NIGHT_SELF;
      $name  .= TalkHTML::GenerateSelfTalk();
      break;

    default:
      //個別発言判定
      if (null !== $talk->location) {
	list($parse_location, $location_id) = Text::Parse($talk->location, ':');
	if ($parse_location == TalkLocation::INDIVIDUAL) {
	  $name .= Text::BRLF . ' -> ' . DB::$USER->ByID($location_id)->GetName();
	  $talk->individual = true;
	}
      }
      break;
    }

    $stack = [
      TalkElement::ID       => $this->GetTalkID($talk),
      TalkElement::SYMBOL   => $this->AddIcon($user, $symbol, $name),
      TalkElement::NAME     => $name . $this->AddTimeName($talk),
      TalkElement::VOICE    => $voice,
      TalkElement::SENTENCE => $talk->sentence,
      TalkElement::CSS_USER => $css
    ];
    if (true === $talk->individual) {
      $stack[TalkElement::CSS_ROW] = TalkVoice::INDIVIDUAL;
    }

    return $this->Register($stack);
  }

  //発言 (霊界)
  private function TalkHeaven(TalkParser $talk, $symbol, $name) {
    if (! $this->flag->open_talk) {
      return false;
    }

    $stack = [
      TalkElement::SYMBOL   => $symbol,
      TalkElement::NAME     => $name . $this->AddTimeName($talk),
      TalkElement::VOICE    => $talk->font_type,
      TalkElement::SENTENCE => $talk->sentence,
      TalkElement::CSS_ROW  => $talk->scene
    ];
    return $this->Register($stack);
  }

  //発言登録 (システムユーザ)
  private function RegisterSystem($str, $talk_id, $css = TalkCSS::SYSTEM) {
    $this->cache .= TalkHTML::GenerateSystem(Text::ConvertLine($str), $talk_id, $css);
    return true;
  }

  //発言登録 (システムメッセージ)
  private function RegisterSystemMessage($str, $talk_id, $css) {
    $this->cache .= TalkHTML::GenerateSystemMessage($str, $talk_id, $css);
    return true;
  }

  //クォート処理
  private function QuoteTalk($str) {
    return GameConfig::QUOTE_TALK ? sprintf(TalkMessage::QUOTE, $str) : $str;
  }

  //アイコン追加
  private function AddIcon(User $user, $symbol, $name) {
    if (RQ::Get()->icon && $name != '') {
      return ImageHTML::GenerateIcon($user) . $symbol;
    } else {
      return $symbol;
    }
  }

  //時刻追加
  private function AddTime(TalkParser $talk) {
    return isset($talk->time) ? TalkHTML::GenerateInfo($talk->date_time) : '';
  }

  //時刻追加 (名前用)
  private function AddTimeName(TalkParser $talk) {
    return isset($talk->time) ? Text::BR . TalkHTML::GenerateInfo($talk->date_time) : '';
  }
}
