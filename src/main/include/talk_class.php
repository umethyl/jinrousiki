<?php
//-- 発言処理クラス --//
class Talk {
  //会話情報取得
  static function Get() {
    $builder = new TalkBuilder('talk');
    foreach (TalkDB::Get() as $talk) $builder->Generate($talk);
    $builder->GenerateTimeStamp();
    return $builder;
  }

  //霊界の会話取得
  static function GetHeaven() {
    //出力条件をチェック
    //if (DB::$SELF->IsDead()) return false; //呼び出し側でチェックするので現在は不要

    $is_open = DB::$ROOM->IsOpenCast(); //霊界公開判定
    $builder = new TalkBuilder('talk');
    foreach (TalkDB::Get(true) as $talk) { //速度を取るため sprintf() を使わない
      $user = DB::$USER->ByUname($talk->uname); //ユーザを取得

      $symbol = '<font color="' . $user->color . '">◆</font>';
      $handle_name = $user->handle_name;
      if ($is_open) $handle_name .= '<span>(' . $talk->uname . ')</span>'; //HN 追加処理

      $builder->AddRaw($symbol, $handle_name, $talk->sentence, $talk->font_type);
    }
    return $builder;
  }

  //会話出力
  static function Output() { return self::Get()->Output(); }

  //霊界の会話出力
  static function OutputHeaven() { return self::GetHeaven()->Output(); }
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

  function __construct($list = null) {
    if (is_array($list)) {
      foreach ($list as $key => $data) $this->$key = $data;
    }
    if (isset($this->time)) $this->date_time = Time::GetDate('(Y/m/d (D) H:i:s)', $this->time);
    $this->Parse();
  }

  //データ解析
  private function Parse($sentence = null) {
    is_null($sentence) ? $sentence = $this->sentence : $this->sentence = $sentence; //初期化処理

    switch ($this->uname) { //システムユーザ系の処理
    case 'system':
      switch ($this->action) {
      case 'MORNING':
	$this->sentence = sprintf(Message::$morning, $sentence);
	return;

      case 'NIGHT':
	$this->sentence = Message::$night;
	return;
      }
      return;

    case 'dummy_boy':
      if ($this->location == 'system') break;
      return;
    }

    if ($this->location == 'system') { //投票データ系
      $action = strtolower($this->action);
      switch ($this->action) { //大文字小文字をきちんと区別してマッチングする
      case 'OBJECTION':
	$this->sentence .= Message::$objection;
	return;

      case 'GAMESTART_DO':
	return;

      case 'STEP_MAGE_DO':
      case 'CHILD_FOX_DO':
	$action = 'mage_do';
	$this->class = 'mage-do';
	break;

      case 'VOODOO_KILLER_DO':
	$this->class = 'mage-do';
	break;

      case 'STEP_GUARD_DO':
	$action = 'guard_do';
	$this->class = 'guard-do';
	break;

      case 'REPORTER_DO':
      case 'ANTI_VOODOO_DO':
	$this->class = 'guard-do';
	break;

      case 'POISON_CAT_DO':
	$action = 'revive_do';
	$this->class = 'revive-do';
	break;

      case 'SPREAD_WIZARD_DO':
	$action = 'wizard_do';
	$this->class = 'wizard-do';
	break;

      case 'STEP_WOLF_EAT':
	$action = 'wolf_eat';
	$this->class = 'wolf-eat';
	break;

      case 'STEP_VAMPIRE_DO':
	$action = 'vampire_do';
	$this->class = 'vampire-do';
	break;

      case 'JAMMER_MAD_DO':
      case 'VOODOO_MAD_DO':
      case 'VOODOO_FOX_DO':
      case 'TRAP_MAD_DO':
      case 'POSSESSED_DO':
	$action = array_shift(explode('_', $action)) . '_do';
	$this->class = 'wolf-eat';
	break;

      case 'SILENT_WOLF_EAT':
      case 'DREAM_EAT':
	$this->class = 'wolf-eat';
	break;

      case 'POISON_CAT_NOT_DO':
	$this->class = 'revive-do';
	$this->sentence .= Message::$revive_not_do;
	return;

      case 'ASSASSIN_NOT_DO':
	$this->class = 'assassin-do';
	$this->sentence .= Message::$assassin_not_do;
	return;

      case 'STEP_NOT_DO':
	$this->class = 'step-do';
	$this->sentence .= Message::$step_not_do;
	return;

      case 'TRAP_MAD_NOT_DO':
	$this->class = 'wolf-eat';
	$this->sentence .= Message::$trap_not_do;
	return;

      case 'POSSESSED_NOT_DO':
	$this->class = 'wolf-eat';
	$this->sentence .= Message::$possessed_not_do;
	return;

      case 'OGRE_NOT_DO':
	$this->class = 'ogre-do';
	$this->sentence .= Message::$ogre_not_do;
	return;

      case 'DEATH_NOTE_NOT_DO':
	$this->class = 'death-note-do';
	$this->sentence .= Message::$death_note_not_do;
	return;

      default:
	$this->class = strtr($action, '_', '-');
	break;
      }
      $this->sentence = ' は ' . $this->sentence . Message::$$action;
      return;
    }
  }
}

//-- 会話生成クラス --//
class TalkBuilder {
  const HEADER = "<table%s class=\"%s\">\n";
  public $cache;
  public $actor;
  public $filter = array();
  public $flag;

  function __construct($class, $id = null) {
    $this->actor = DB::$SELF->GetVirtual(); //仮想ユーザを取得

    //観戦モード判定
    if ((is_null($this->actor->live) || ! DB::$ROOM->IsOpenCast()) && ! DB::$ROOM->IsFinished()) {
      //本人視点が変化するタイプに仮想役職をセットする
      $is_day = DB::$ROOM->IsDay();
      $stack  = array('blinder' => $is_day, 'earplug' => $is_day, 'deep_sleep' => true);
      foreach ($stack as $role => $flag) {
	if (($flag && DB::$ROOM->IsEvent($role)) || DB::$ROOM->IsOption($role)) {
	  $this->actor->virtual_live = true;
	  $this->actor->role_list[]  = $role;
	}
      }
    }

    $this->LoadFilter();
    $this->SetFlag();
    $this->Begin($class, $id);
  }

  //テーブルヘッダ生成
  function Begin($class, $id = null) {
    $this->cache = sprintf(self::HEADER, is_null($id) ? '' : ' id="' . $id . '"', $class);
  }

  //発言生成
  function Generate(TalkParser $talk) {
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
	$this->SetFlag();
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
      $symbol = '';
      $name   = '';
      $actor->id = 0;
    }
    else {
      $color  = isset($talk->color) ? $talk->color : $actor->color;
      $symbol = '<font color="' . $color . '">◆</font>';
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
      if (isset($talk->time)) {
	$str .= ' <span class="date-time">' . $talk->date_time . '</span>';
      }

      if (! isset($talk->action)) return $this->AddSystem($str); //標準
      switch ($talk->action) { //投票情報
      case 'GAMESTART_DO': //現在は不使用
	return true;

      case 'OBJECTION': //「異議」ありは常時表示
	return $this->AddSystemMessage('objection-' . $actor->sex, $name . $str);

      case 'MORNING':
      case 'NIGHT':
	return $this->AddSystem($str);

      default: //ゲーム開始前の投票 (例：KICK) は常時表示
	return $this->flag->open_talk || DB::$ROOM->IsBeforeGame() ?
	  $this->AddSystemMessage($talk->class, $name . $str) : false;
      }
      return false;

    case 'dummy_boy': //身代わり君専用システムメッセージ
      $str = sprintf('◆%s　%s', $real_user->handle_name, $talk->sentence);
      if (GameConfig::QUOTE_TALK) $str = sprintf('「%s」', $str);
      if (isset($talk->time)) {
	$str .= ' <span class="date-time">' . $talk->date_time . '</span>';
      }
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
	  $talk->sentence = Message::$common_talk;
	}
      }
      return $this->Add($actor, $talk, $real);

    case 'night':
      if ($this->flag->open_talk) {
	$class = '';
	$voice = $talk->font_type;
	switch ($talk->location) {
	case 'common':
	  $name .= '<span>(共有者)</span>';
	  $class = 'night-common';
	  $voice .= ' ' . $class;
	  break;

	case 'wolf':
	  $name .= '<span>(人狼)</span>';
	  $class = 'night-wolf';
	  $voice .= ' ' . $class;
	  break;

	case 'mad':
	  $name .= '<span>(囁き狂人)</span>';
	  $class = 'night-wolf';
	  $voice .= ' ' . $class;
	  break;

	case 'fox':
	  $name .= '<span>(妖狐)</span>';
	  $class = 'night-fox';
	  $voice .= ' ' . $class;
	  break;

	case 'self_talk':
	  $name .= '<span>の独り言</span>';
	  $class = 'night-self-talk';
	  break;
	}
	$str = $talk->sentence; //改行を入れるため再セット
	if (isset($talk->time)) $name .= '<br><span>' . $talk->date_time . '</span>';
	if (RQ::Get()->icon) $symbol = Icon::GetUserIcon($actor) . $symbol;
	return $this->AddRaw($symbol, $name, $str, $voice, '', $class);
      }
      else {
	$mind_read = false; //特殊発言透過判定
	RoleManager::SetActor($actor);
	foreach (RoleManager::Load('mind_read') as $filter) {
	  $mind_read |= $filter->IsMindRead();
	  if ($mind_read) break;
	}

	if (! $mind_read) {
	  RoleManager::SetActor($this->actor);
	  foreach (RoleManager::Load('mind_read_active') as $filter) {
	    $mind_read |= $filter->IsMindReadActive($actor);
	    if ($mind_read) break;
	  }
	}

	if (! $mind_read) {
	  RoleManager::SetActor($real_user);
	  foreach (RoleManager::Load('mind_read_possessed') as $filter) {
	    $mind_read |= $filter->IsMindReadPossessed($actor);
	    if ($mind_read) break;
	  }
	}

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
      if (isset($talk->time)) $name .= '<br><span>' . $talk->date_time . '</span>';
      return $this->AddRaw($symbol, $name, $talk->sentence, $talk->font_type, $talk->scene);

    default:
      return $this->Add($actor, $talk, $real);
    }
  }

  //[村立て / ゲーム開始 / ゲーム終了] 時刻生成
  function GenerateTimeStamp() {
    switch (DB::$ROOM->scene) {
    case 'beforegame':
      $type     = 'establish_datetime'; //村立て時刻
      $sentence = '村作成';
      break;

    case 'night':
      if (! DB::$ROOM->IsDate(1)) return false;
      $type     = 'start_datetime'; //ゲーム開始時刻
      $sentence = 'ゲーム開始';
      break;

    case 'aftergame':
      $type     = 'finish_datetime'; //ゲーム終了時刻
      $sentence = 'ゲーム終了';
      break;

    default:
      return false;
    }

    if (is_null($time = RoomDB::Fetch($type))) return false;
    $talk = new TalkParser();
    $talk->sentence = $sentence . '：' . Time::ConvertTimeStamp($time);
    $talk->uname    = 'system';
    $talk->scene    = DB::$ROOM->scene;
    $talk->location = 'system';
    $this->Generate($talk);
  }

  //基礎発言
  function AddRaw($symbol, $user_info, $str, $voice, $row_class = '', $user_class = '',
		  $say_class = '') {
    if ($row_class  != '') $row_class  = ' ' . $row_class;
    if ($user_class != '') $user_class = ' ' . $user_class;
    if ($say_class  != '') $say_class  = ' ' . $say_class;
    Text::Line($str);
    if (GameConfig::QUOTE_TALK) $str = '「' . $str . '」';

    $this->cache .= <<<EOF
<tr class="user-talk{$row_class}">
<td class="user-name{$user_class}">{$symbol}{$user_info}</td>
<td class="say{$say_class} {$voice}">{$str}</td>
</tr>

EOF;
    return true;
  }

  //標準発言
  function Add(User $user, TalkParser $talk, $real = null) {
    //表示情報を抽出
    $color  = isset($talk->color) ? $talk->color : $user->color;
    $symbol = '<font style="color:' . $color . '">◆</font>';
    $name   = isset($talk->handle_name) ? $talk->handle_name : $user->handle_name;
    if (RQ::Get()->add_role && $user->id != 0) { //役職表示モード対応
      $real = $talk->scene == 'heaven' ? $user :
	(isset($real) ? $real : DB::$USER->ByReal($user->id));
      $name .= $real->GenerateShortRoleName();
    }
    elseif (DB::$ROOM->IsFinished() && RQ::Get()->name) { //ユーザ名表示モード
      $name .= $user->GenerateShortRoleName();
    }
    if (DB::$ROOM->IsNight() &&
	(($talk->location == 'self_talk' && ! $user->IsRole('dummy_common')) ||
	 $user->IsRole('leader_common', 'mind_read', 'mind_open'))) {
      $name .= '<span>の独り言</span>';
    }
    $str   = $talk->sentence;
    $voice = $talk->font_type;
    //発言フィルタ処理
    foreach ($this->filter as $filter) $filter->FilterTalk($user, $name, $voice, $str);
    if (RQ::Get()->icon && $name != '') $symbol = Icon::GetUserIcon($user) . $symbol;
    if (isset($talk->date_time)) $name .= '<br><span>' . $talk->date_time . '</span>';
    return $this->AddRaw($symbol, $name, $str, $voice);
  }

  //システムユーザ発言
  function AddSystem($str, $class = 'system-user') {
    Text::Line($str);
    $this->cache .= <<<EOF
<tr>
<td class="{$class}" colspan="2">{$str}</td>
</tr>

EOF;
    return true;
  }

  //システムメッセージ
  function AddSystemMessage($class, $str, $add_class = '') {
    if ($add_class != '') $add_class = ' ' . $add_class;
    $this->cache .= <<<EOF
<tr class="system-message{$add_class}">
<td class="{$class}" colspan="2">{$str}</td>
</tr>

EOF;
    return true;
  }

  //キャッシュリセット
  function Refresh() {
    $str = $this->cache.'</table>'."\n";
    $this->cache = '';
    return $str;
  }

  //出力処理
  function Output() { echo $this->Refresh(); }

  //役職情報ロード
  private function LoadFilter() {
    $this->actor->virtual_live |= false;
    RoleManager::SetActor($this->actor);
    RoleManager::SetStack('viewer', $this->actor);
    RoleManager::SetStack('builder', $this);
    $this->filter = RoleManager::Load('talk');
  }

  //フィルタ用フラグセット
  private function SetFlag() {
    $this->flag->dummy_boy  = DB::$SELF->IsDummyBoy();
    $this->flag->common     = $this->actor->IsCommon(true);
    $this->flag->wolf       = DB::$SELF->IsWolf(true) || $this->actor->IsRole('whisper_mad');
    $this->flag->fox        = DB::$SELF->IsFox(true);
    $this->flag->lovers     = DB::$SELF->IsLovers();
    $this->flag->mind_read  = DB::$ROOM->date > 1 &&
      (DB::$ROOM->single_view_mode || DB::$SELF->IsLive());

    $this->flag->deep_sleep = $this->actor->IsRole('deep_sleep');
    foreach (array('whisper_ringing', 'howl_ringing', 'sweet_ringing') as $role) { //耳鳴
      $this->flag->$role = $this->actor->IsRole($role) && ! $this->flag->deep_sleep;
    }
    $this->flag->sweet_ringing  = $this->flag->sweet_ringing && DB::$ROOM->date > 1;
    $this->flag->common_whisper = ! DB::$SELF->IsRole('dummy_common') && ! $this->flag->deep_sleep;
    $this->flag->wolf_howl      = ! DB::$SELF->IsRole('mind_scanner') && ! $this->flag->deep_sleep;
    if (DB::$ROOM->watch_mode) $this->flag->wolf |= RQ::Get()->wolf_sight; //狼視点モード

    //発言完全公開フラグ
    /*
      + ゲーム終了後は全て表示
      + 霊界表示オン状態の死者には全て表示
      + 霊界表示オフ状態は観戦者と同じ (投票情報は表示しない)
    */
    $this->flag->open_talk = DB::$ROOM->IsOpenData();

    foreach (array('common', 'wolf', 'fox') as $type) { //身代わり君の上書き判定
      $this->flag->$type |= $this->flag->dummy_boy;
    }
  }
}

//-- DB アクセス (Talk 拡張) --//
class TalkDB {
  //発言取得
  function Get($heaven = false) {
    if (RQ::Get()->IsVirtualRoom()) return RQ::GetTest()->talk;

    $format = 'SELECT %s FROM %s WHERE room_no = ?';
    $select = 'scene, location, uname, action, sentence, font_type';
    switch (DB::$ROOM->scene) {
    case 'beforegame':
      $table = sprintf('talk_%s', DB::$ROOM->scene);
      $select .= ', handle_name, color';
      break;

    case 'aftergame':
      $table = sprintf('talk_%s', DB::$ROOM->scene);
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
SELECT uname, sentence FROM talk WHERE room_no = ? AND date = ? AND scene = ? AND uname != ?
ORDER BY id DESC LIMIT 5
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, 'dummy_boy'));
    return DB::FetchAssoc();
  }

  //会話経過時間取得
  static function GetSpendTime() {
    $query = 'SELECT SUM(spend_time) FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene));
    return (int)DB::FetchResult();
  }

  //最終シーンの夜の発言の有無を検出
  function ExistsLastNight() {
    $query = 'SELECT id FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, 'night'));
    return DB::Count() > 0;
  }
}
