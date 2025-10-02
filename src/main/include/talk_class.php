<?php
//-- 発言処理クラス --//
class Talk {
  //会話取得
  static function Get() {
    $builder = new TalkBuilder('talk');
    foreach (TalkDB::Get() as $talk) $builder->Generate($talk);
    $builder->GenerateTimeStamp();
    return $builder;
  }

  //会話取得 (霊界用)
  static function GetHeaven() {
    //出力条件をチェック
    //if (DB::$SELF->IsDead()) return false; //呼び出し側でチェックするので現在は不要

    $builder = new TalkBuilder('talk');
    $builder->flag->open_cast = DB::$ROOM->IsOpenCast(); //霊界公開判定
    foreach (TalkDB::Get(true) as $talk) $builder->GenerateHeaven($talk);
    return $builder;
  }

  //会話出力
  static function Output() {
    return self::Get()->Output();
  }

  //会話出力 (霊界用)
  static function OutputHeaven() {
    return self::GetHeaven()->Output();
  }
}

//-- 発言パーサ --//
class TalkParser {
  public $scene;
  public $location;
  public $uname;
  public $action;
  public $sentence;
  public $font_type;
  public $time;
  public $date_time;

  public function __construct($list = null) {
    if (is_array($list)) {
      foreach ($list as $key => $data) {
	$this->$key = $data;
      }
    }
    if (isset($this->time)) $this->date_time = Time::GetDate('Y/m/d (D) H:i:s', $this->time);
    $this->Parse();
  }

  //データ解析
  private function Parse() {
    switch ($this->uname) { //システムユーザ系の処理
    case 'system':
      switch ($this->action) {
      case 'MORNING':
	$this->sentence = sprintf(TalkMessage::MORNING, $this->sentence);
	return;

      case 'NIGHT':
	$this->sentence = TalkMessage::NIGHT;
	return;
      }
      return;

    case 'dummy_boy':
      if ($this->location == 'system') $this->ParseSystem();
      return;
    }

    if ($this->location == 'system') $this->ParseSystem();
  }

  //投票データ解析
  private function ParseSystem() {
    $action = $this->action;
    switch ($this->action) { //大文字小文字をきちんと区別してマッチングする
    case 'OBJECTION':
      $this->sex      = $this->sentence;
      $this->sentence = VoteTalkMessage::${$this->action};
      return;

    case 'STEP_MAGE_DO':
    case 'CHILD_FOX_DO':
      $action = 'MAGE_DO';
      $this->class = 'mage-do';
      break;

    case 'VOODOO_KILLER_DO':
      $this->class = 'mage-do';
      break;

    case 'STEP_GUARD_DO':
      $action = 'GUARD_DO';
      $this->class = 'guard-do';
      break;

    case 'REPORTER_DO':
    case 'ANTI_VOODOO_DO':
      $this->class = 'guard-do';
      break;

    case 'POISON_CAT_DO':
      $action = 'REVIVE_DO';
      $this->class = 'revive-do';
      break;

    case 'STEP_ASSASSIN_DO':
      $action = 'ASSASSIN_DO';
      $this->class = 'assassin-do';
      break;

    case 'STEP_SCANNER_DO':
      $action = 'MIND_SCANNER_DO';
      $this->class = 'mind-scanner-do';
      break;

    case 'SPREAD_WIZARD_DO':
      $action = 'WIZARD_DO';
      $this->class = 'wizard-do';
      break;

    case 'STEP_WOLF_EAT':
      $action = 'WOLF_EAT';
      $this->class = 'wolf-eat';
      break;

    case 'STEP_VAMPIRE_DO':
      $action = 'VAMPIRE_DO';
      $this->class = 'vampire-do';
      break;

    case 'JAMMER_MAD_DO':
    case 'VOODOO_MAD_DO':
    case 'VOODOO_FOX_DO':
    case 'TRAP_MAD_DO':
    case 'POSSESSED_DO':
      $action = array_shift(explode('_', $action)) . '_DO';
      $this->class = 'wolf-eat';
      break;

    case 'SILENT_WOLF_EAT':
    case 'DREAM_EAT':
      $this->class = 'wolf-eat';
      break;

    case 'POISON_CAT_NOT_DO':
      $this->class = 'revive-do';
      $this->sentence .= VoteTalkMessage::$REVIVE_NOT_DO;
      return;

    case 'ASSASSIN_NOT_DO':
      $this->class = 'assassin-do';
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case 'STEP_NOT_DO':
      $this->class = 'step-do';
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case 'TRAP_MAD_NOT_DO':
      $this->class = 'wolf-eat';
      $this->sentence .= VoteTalkMessage::$TRAP_NOT_DO;
      return;

    case 'POSSESSED_NOT_DO':
      $this->class = 'wolf-eat';
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case 'OGRE_NOT_DO':
      $this->class = 'ogre-do';
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    case 'DEATH_NOTE_NOT_DO':
      $this->class = 'death-note-do';
      $this->sentence .= VoteTalkMessage::${$this->action};
      return;

    default:
      $this->class = strtolower(strtr($action, '_', '-'));
      break;
    }
    $this->sentence = sprintf(VoteTalkMessage::FORMAT, $this->sentence . VoteTalkMessage::$$action);
    return;
  }
}

//-- 会話生成クラス --//
class TalkBuilder {
  public  $filter = array();
  public  $flag;
  private $actor;
  private $cache;

  public function __construct($class, $id = null) {
    $this->actor = DB::$SELF->GetVirtual(); //仮想ユーザを取得
    $this->LoadVirtualRole();
    $this->LoadFilter();
    $this->LoadFlag();
    $this->Begin($class, $id);
  }

  //テーブルヘッダ生成
  public function Begin($class, $id = null) {
    $this->cache = TalkHTML::GenerateHeader($class, $id);
  }

  //発言生成
  public function Generate(TalkParser $talk) {
    //Text::p($talk);
    //発言ユーザを取得
    /*
      $uname は必ず $talk から取得すること。
      DB::$USER にはシステムユーザー 'system' が存在しないため、$actor は常に null になっている。
      速度を取るため sprintf() を使わないこと
    */
    $actor = DB::$USER->ByUname($talk->uname);
    $real  = $actor;
    if (DB::$ROOM->log_mode && isset($talk->role_id)) { //役職スイッチ処理
      //閲覧者のスイッチに伴う可視性のリロード処理
      if ($actor->ChangePlayer($talk->role_id) && $actor->IsSame($this->actor)) {
	//Text::p($talk->role_id, 'Switch');
	$this->LoadFilter();
	$this->LoadFlag();
      }
    }
    switch ($talk->scene) {
    case 'day':
    case 'night':
      $virtual = DB::$USER->ByVirtual($actor->id);
      if (! $actor->IsSame($virtual)) $actor = $virtual;
      break;
    }

    //基本パラメータを取得
    if ($talk->uname == 'system') {
      $symbol    = '';
      $name      = '';
      $actor->id = 0;
    }
    else {
      $symbol = TalkHTML::GenerateSymbol(isset($talk->color) ? $talk->color : $actor->color);
      $name   = isset($talk->handle_name) ? $talk->handle_name : $actor->handle_name;
    }

    //実ユーザを取得
    if (RQ::Get()->add_role && $actor->id > 0) { //役職表示モード対応
      $real_user = isset($real) ? $real : $actor;
      $name .= $real_user->GenerateShortRoleName($talk->scene == 'heaven');
    }
    else {
      $real_user = DB::$USER->ByRealUname($talk->uname);
    }

    switch ($talk->location) {
    case 'system': //システムメッセージ
      $str = $talk->sentence;
      if (isset($talk->time)) $str .= TalkHTML::GenerateTime($talk->date_time);

      if (! isset($talk->action)) return $this->AddSystem($str); //標準
      switch ($talk->action) { //投票情報
      case 'OBJECTION': //「異議」ありは常時表示
	$sex = empty($talk->sex) ? $actor->sex : $talk->sex;
	return $this->AddSystemMessage($name . $str, 'objection-' . $sex);

      case 'MORNING':
      case 'NIGHT':
	return $this->AddSystem($str);

      default: //ゲーム開始前の投票 (例：KICK) は常時表示
	if ($this->flag->open_talk || DB::$ROOM->IsBeforeGame()) {
	  return $this->AddSystemMessage($name . $str, $talk->class);
	}
	return false;
      }
      return false;

    case 'dummy_boy': //身代わり君専用システムメッセージ
      $str = Message::SYMBOL . $real_user->handle_name . Message::SPACER . $talk->sentence;
      if (GameConfig::QUOTE_TALK) $str = sprintf(TalkMessage::QUOTE, $str);
      if (isset($talk->time)) $str .= TalkHTML::GenerateTime($talk->date_time);
      return $this->AddSystem($str, 'dummy-boy');
    }

    switch ($talk->scene) {
    case 'day':
      //強風判定 (身代わり君と本人は対象外)
      if (DB::$ROOM->IsEvent('blind_talk_day') &&
	  ! $this->flag->dummy_boy && ! $this->actor->IsSameName($talk->uname)) {
	//位置判定 (観戦者以外の上下左右)
	$viewer = $this->actor->id;
	$target = $actor->id;
	if (is_null($viewer) ||
	    ! (abs($target - $viewer) == 5 ||
	       ($target == $viewer - 1 && ($target % 5) != 0) ||
	       ($target == $viewer + 1 && ($viewer % 5) != 0))) {
	  $talk->sentence = RoleTalkMessage::COMMON_TALK;
	}
      }
      return $this->Add($actor, $talk, $real);

    case 'night':
      if ($this->flag->open_talk) {
	$class = '';
	$voice = $talk->font_type;
	switch ($talk->location) {
	case 'common':
	  $class  = 'night-common';
	  $name  .= TalkHTML::GenerateInfo(TalkMessage::COMMON);
	  $voice .= ' ' . $class;
	  break;

	case 'wolf':
	  $class  = 'night-wolf';
	  $name  .= TalkHTML::GenerateInfo(TalkMessage::WOLF);
	  $voice .= ' ' . $class;
	  break;

	case 'mad':
	  $class  = 'night-wolf';
	  $name  .= TalkHTML::GenerateInfo(TalkMessage::MAD);
	  $voice .= ' ' . $class;
	  break;

	case 'fox':
	  $class  = 'night-fox';
	  $name  .= TalkHTML::GenerateInfo(TalkMessage::FOX);
	  $voice .= ' ' . $class;
	  break;

	case 'self_talk':
	  $class  = 'night-self-talk';
	  $name  .= TalkHTML::GenerateSelfTalk();
	  break;
	}
	$str = $talk->sentence; //改行を入れるため再セット
	if (isset($talk->time)) $name .= Text::BR . TalkHTML::GenerateInfo($talk->date_time);
	if (RQ::Get()->icon) $symbol = Icon::GetUserIcon($actor) . $symbol;

	$stack = array(
	  'str'        => $str,
	  'symbol'     => $symbol,
	  'user_info'  => $name,
	  'voice'      => $voice,
	  'user_class' => $class
        );
	return $this->AddRaw($stack);
      }
      else {
	$mind_read = $this->IsMindRead($actor, $real_user); //特殊発言透過判定
	RoleManager::SetActor($actor);
	switch ($talk->location) {
	case 'common': //共有者
	  if ($this->flag->common || $mind_read) return $this->Add($actor, $talk, $real);

	  $filter = RoleManager::LoadMain($actor);
	  if (! method_exists($filter, 'Whisper')) return; //player スイッチによる不整合対策

	  if ($filter->Whisper($this, $talk->font_type)) return;
	  foreach (RoleManager::Load('talk_whisper') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }
	  return false;

	case 'wolf': //人狼
	  if ($this->flag->wolf || $mind_read) return $this->Add($actor, $talk, $real);

	  $filter = RoleManager::LoadMain($actor);
	  if (! method_exists($filter, 'Howl')) return; //player スイッチによる不整合対策

	  if ($filter->Howl($this, $talk->font_type)) return;
	  foreach (RoleManager::Load('talk_whisper') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }
	  return false;

	case 'mad': //囁き狂人
	  if ($this->flag->wolf || $mind_read) return $this->Add($actor, $talk, $real);

	  foreach (RoleManager::Load('talk_whisper') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }
	  return false;

	case 'fox': //妖狐
	  if ($this->flag->fox || $mind_read) return $this->Add($actor, $talk, $real);

	  RoleManager::SetActor(DB::$SELF);
	  foreach (RoleManager::Load('talk_fox') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }

	  RoleManager::SetActor($actor);
	  foreach (RoleManager::Load('talk_whisper') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }
	  return false;

	case 'self_talk': //独り言
	  if ($this->flag->dummy_boy || $mind_read || $this->actor->IsSameName($talk->uname)) {
	    return $this->Add($actor, $talk, $real);
	  }

	  foreach (RoleManager::Load('talk_self') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }

	  RoleManager::SetActor($this->actor);
	  foreach (RoleManager::Load('talk_ringing') as $filter) {
	    if ($filter->Whisper($this, $talk->font_type)) return;
	  }
	  return false;
	}
      }
      return false;

    case 'heaven':
      if (! $this->flag->open_talk) return false;
      if (isset($talk->time)) $name .= Text::BR . TalkHTML::GenerateInfo($talk->date_time);

      $stack = array(
	'str'       => $talk->sentence,
	'symbol'    => $symbol,
	'user_info' => $name,
	'voice'     => $talk->font_type,
	'row_class' => $talk->scene
      );
      return $this->AddRaw($stack);

    default:
      return $this->Add($actor, $talk, $real);
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

    $stack = array(
      'str'       => $talk->sentence,
      'symbol'    => TalkHTML::GenerateSymbol($user->color),
      'user_info' => $handle_name,
      'voice'     => $talk->font_type
    );
    return $this->AddRaw($stack);
  }

  //時刻生成
  public function GenerateTimeStamp() {
    switch (DB::$ROOM->scene) {
    case 'day': //OP の昼限定
      if (! DB::$ROOM->IsDate(1)) return false;
      $type     = 'start_datetime'; //ゲーム開始時刻
      $sentence = TalkMessage::GAME_START;
      break;

    case 'beforegame':
      $type     = 'establish_datetime'; //村立て時刻
      $sentence = TalkMessage::ESTABLISH;
      break;

    case 'night':
      if (! DB::$ROOM->IsDate(1)) return false;
      $type     = 'start_datetime'; //ゲーム開始時刻
      $sentence = TalkMessage::GAME_START;
      break;

    case 'aftergame':
      $type     = 'finish_datetime'; //ゲーム終了時刻
      $sentence = TalkMessage::GAME_END;
      break;

    default:
      return false;
    }

    if (is_null($time = RoomDB::Fetch($type))) return false;
    $talk = new TalkParser();
    $talk->sentence = $sentence . Time::ConvertTimeStamp($time);
    $talk->uname    = 'system';
    $talk->scene    = DB::$ROOM->scene;
    $talk->location = 'system';
    $this->Generate($talk);
  }

  //基礎発言
  public function AddRaw(array $list) {
    extract($list);

    $stack = array();
    foreach (array('row_class', 'user_class', 'say_class') as $key) {
      if (isset($$key) && $$key != '') {
	$$key = ' ' . $$key;
      } else {
	$$key = '';
      }
      $stack[$key] = $$key;
    }
    Text::Line($str);
    if (GameConfig::QUOTE_TALK) $str = sprintf(TalkMessage::QUOTE, $str);

    foreach (array('str', 'symbol', 'user_info', 'voice') as $key) {
      $stack[$key] = $$key;
    }
    $this->cache .= TalkHTML::Generate($stack);
    return true;
  }

  //デバッグ用
  public function AddDebug($str) {
    $stack = array('str' => $str, 'symbol' => '', 'user_info' => '', 'voice' => null);
    return $this->AddRaw($stack);
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

  //仮想役職セット (本人視点が変化するタイプにセットする)
  private function LoadVirtualRole() {
    //観戦モード判定
    if (DB::$ROOM->IsFinished() || (isset($this->actor->live) && DB::$ROOM->IsOpenCast())) {
      return;
    }

    $is_day = DB::$ROOM->IsDay();
    $stack  = array('blinder' => $is_day, 'earplug' => $is_day, 'deep_sleep' => true);
    foreach ($stack as $role => $flag) {
      if (($flag && DB::$ROOM->IsEvent($role)) || DB::$ROOM->IsOption($role)) {
	$this->actor->virtual_live = true;
	$this->actor->role_list[]  = $role;
      }
    }
  }

  //役職情報ロード
  private function LoadFilter() {
    $this->actor->virtual_live |= false;
    RoleManager::SetActor($this->actor);
    RoleManager::SetStack('viewer', $this->actor);
    RoleManager::SetStack('builder', $this);
    $this->filter = RoleManager::Load('talk');
  }

  //フィルタ用フラグセット
  private function LoadFlag() {
    $flag = new StdClass();

    /* 基本情報 */
    $flag->dummy_boy = DB::$SELF->IsDummyBoy();
    $flag->common    = $this->actor->IsCommon(true);
    $flag->wolf      = DB::$SELF->IsWolf(true) || $this->actor->IsRole('whisper_mad');
    $flag->fox       = DB::$SELF->IsFox(true);
    $flag->lovers    = DB::$SELF->IsLovers();
    $flag->mind_read = DB::$ROOM->date > 1 && (DB::$ROOM->single_view_mode || DB::$SELF->IsLive());
    $flag->open_talk = DB::$ROOM->IsOpenData();

    if (DB::$ROOM->watch_mode) $flag->wolf |= RQ::Get()->wolf_sight; //狼視点モード
    foreach (array('common', 'wolf', 'fox') as $type) { //身代わり君の上書き判定
      $flag->$type |= $flag->dummy_boy;
    }

    /* 耳鳴り関連 */
    $flag->deep_sleep = $this->actor->IsRole('deep_sleep');
    foreach (array('whisper_ringing', 'howl_ringing', 'sweet_ringing') as $role) { //耳鳴
      $flag->$role = $this->actor->IsRole($role) && ! $flag->deep_sleep;
    }
    $flag->sweet_ringing  = $flag->sweet_ringing && DB::$ROOM->date > 1;
    $flag->common_whisper = ! DB::$SELF->IsRole('dummy_common') && ! $flag->deep_sleep;
    $flag->wolf_howl      = ! DB::$SELF->IsRole('mind_scanner') && ! $flag->deep_sleep;

    $this->flag = $flag;
  }

  //特殊発言透過判定
  private function IsMindRead(User $actor, User $real) {
    RoleManager::SetActor($actor);
    foreach (RoleManager::Load('mind_read') as $filter) {
      if ($filter->IsMindRead()) return true;
    }

    RoleManager::SetActor($this->actor);
    foreach (RoleManager::Load('mind_read_active') as $filter) {
      if ($filter->IsMindReadActive($actor)) return true;
    }

    RoleManager::SetActor($real);
    foreach (RoleManager::Load('mind_read_possessed') as $filter) {
      if ($filter->IsMindReadPossessed($actor)) return true;
    }

    return false;
  }

  //標準発言
  private function Add(User $user, TalkParser $talk, $real = null) {
    //表示情報を抽出
    $symbol = TalkHTML::GenerateSymbol(isset($talk->color) ? $talk->color : $user->color);
    $name   = isset($talk->handle_name) ? $talk->handle_name : $user->handle_name;
    if (RQ::Get()->add_role && $user->id != 0) { //役職表示モード対応
      if ($talk->scene == 'heaven') {
	$real = $user;
      } elseif (is_null($real)) {
	$real = DB::$USER->ByReal($user->id);
      }
      $name .= $real->GenerateShortRoleName();
    }
    elseif (RQ::Get()->name && DB::$ROOM->IsFinished()) { //ユーザ名表示モード
      $name .= $user->GenerateShortRoleName();
    }

    if (DB::$ROOM->IsNight() &&
	(($talk->location == 'self_talk' && ! $user->IsRole('dummy_common')) ||
	 $user->IsRole('leader_common', 'mind_read', 'mind_open'))) {
      $name .= TalkHTML::GenerateSelfTalk();
    }
    $str   = $talk->sentence;
    $voice = $talk->font_type;
    //発言フィルタ処理
    foreach ($this->filter as $filter) $filter->FilterTalk($user, $name, $voice, $str);

    if (RQ::Get()->icon && $name != '') $symbol = Icon::GetUserIcon($user) . $symbol;
    if (isset($talk->time)) $name .= Text::BR . TalkHTML::GenerateInfo($talk->date_time);

    $stack = array(
      'str'        => $str,
      'symbol'     => $symbol,
      'user_info'  => $name,
      'voice'      => $voice
    );
    return $this->AddRaw($stack);
  }

  //システムユーザ発言
  private function AddSystem($str, $class = 'system-user') {
    Text::Line($str);
    $this->cache .= TalkHTML::GenerateSystem($str, $class);
    return true;
  }

  //システムメッセージ
  private function AddSystemMessage($str, $class) {
    $this->cache .= TalkHTML::GenerateSystemMessage($str, $class);
    return true;
  }
}

//-- DB アクセス (Talk 拡張) --//
class TalkDB {
  //発言取得
  static function Get($heaven = false) {
    if (RQ::Get()->IsVirtualRoom()) return RQ::GetTest()->talk;

    $format = 'SELECT %s FROM %s WHERE room_no = ?';
    $select = 'scene, location, uname, action, sentence, font_type';
    switch (DB::$ROOM->scene) {
    case 'beforegame':
      $table = 'talk_' . DB::$ROOM->scene;
      $select .= ', handle_name, color';
      break;

    case 'aftergame':
      $table = 'talk_' . DB::$ROOM->scene;
      break;

    default:
      $table = 'talk';
      if (DB::$ROOM->log_mode) $select .= ', role_id';
      break;
    }

    if ($heaven) {
      $table = 'talk';
      $scene = 'heaven';
    } else {
      $scene = DB::$ROOM->scene;
    }

    $query = sprintf($format, $select, $table);
    $list  = array(DB::$ROOM->id);
    if (! $heaven) {
      $query .= ' AND date = ?';
      $list[] = DB::$ROOM->date;
    }
    $query .= ' AND scene = ? ORDER BY id DESC';
    $list[] = $scene;

    if (! DB::$ROOM->IsPlaying()) $query .= sprintf(' LIMIT 0, %d', GameConfig::LIMIT_TALK);
    DB::Prepare($query, $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (ログ用)
  static function GetLog($set_date, $set_scene) {
    $format = 'SELECT %s FROM %s WHERE room_no = ? AND ';
    $list   = array(DB::$ROOM->id);
    $select = 'scene, location, uname, action, sentence, font_type';
    $table  = 'talk';
    if (RQ::Get()->time) $select .= ', time';

    switch ($set_scene) {
    case 'beforegame':
      $table  .= '_' . $set_scene;
      $select .= ', handle_name, color';
      $format .= 'scene = ?';
      $list[] = $set_scene;
      break;

    case 'aftergame':
      $table .= '_' . $set_scene;
      $format .= 'scene = ?';
      $list[] = $set_scene;
      break;

    case 'heaven_only':
      $format .= 'date = ? AND (scene = ? OR uname = ?)';
      array_push($list, $set_date, 'heaven', 'system');
      break;

    default:
      $select .= ', role_id';
      $format .= 'date = ? AND scene IN ';
      $list[] = $set_date;

      $stack = array('day', 'night');
      if (RQ::Get()->heaven_talk) $stack[] = 'heaven';
      $format .= sprintf('(%s)', implode(',', array_fill(0, count($stack), '?')));
      $list = array_merge($list, $stack);
      break;
    }
    if (DB::$ROOM->personal_mode) { //個人結果表示モード
      $format .= ' AND uname = ?';
      $list[] = 'system';
    }
    $query = sprintf($format, $select, $table);
    $query .= ' ORDER BY id ' . (RQ::Get()->reverse_log ? 'ASC' : 'DESC'); //ログの表示順

    DB::Prepare($query, $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (直近限定)
  static function GetRecent() {
    $query = <<<EOF
SELECT uname, sentence FROM talk WHERE room_no = ? AND date = ? AND scene = ? AND location IS NULL
ORDER BY id DESC LIMIT 5
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene));
    return DB::FetchAssoc();
  }

  //会話経過時間取得
  static function GetSpendTime() {
    $query = 'SELECT SUM(spend_time) FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene));
    return (int)DB::FetchResult();
  }

  //最終シーンの夜の発言の有無を検出
  static function ExistsLastNight() {
    $query = 'SELECT id FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, 'night'));
    return DB::Count() > 0;
  }
}

//-- HTML 生成クラス (Talk 拡張) --//
class TalkHTML {
  /* 発言データ */
  //発言生成
  static function Generate(array $list) {
    extract($list);
    $format = <<<EOF
<tr class="user-talk%s">
<td class="user-name%s">%s%s</td>
<td class="say%s %s">%s</td>
</tr>
EOF;
    return sprintf($format . Text::LF, $row_class,
		   $user_class, $symbol, $user_info,
		   $say_class, $voice, $str);
  }

  //システムユーザ
  static function GenerateSystem($str, $class) {
    $format = <<<EOF
<tr>
<td class="%s" colspan="2">%s</td>
</tr>
EOF;
    return sprintf($format . Text::LF, $class, $str);
  }

  //システムメッセージ
  static function GenerateSystemMessage($str, $class) {
    $format = <<<EOF
<tr class="system-message">
<td class="%s" colspan="2">%s</td>
</tr>
EOF;
    return sprintf($format . Text::LF, $class, $str);
  }

  /* 個別データ */
  //ヘッダー生成
  static function GenerateHeader($class, $id = null) {
    $id = is_null($id) ? '' : sprintf(' id="%s"', $id);
    return sprintf('<table%s class="%s">' . Text::LF, $id, $class);
  }

  //フッター生成
  static function GenerateFooter() {
    return '</table>' . Text::LF;
  }

  //ユーザ名生成
  static function GenerateSymbol($color) {
    return '<font color="' . $color . '">' . Message::SYMBOL . '</font>';
  }

  //追加情報生成
  static function GenerateInfo($str) {
    return '<span>(' . $str . ')</span>';
  }

  //追加情報生成 (独り言)
  static function GenerateSelfTalk() {
    return '<span>' . TalkMessage::SELF_TALK . '</span>';
  }

  //時刻生成
  static function GenerateTime($time) {
    return ' <span class="date-time">(' . $time . ')</span>';
  }
}
