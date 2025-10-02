<?php
//-- 個別ユーザクラス --//
class User {
  public $id;
  public $uname;
  public $role;
  public $main_role;
  public $live;
  public $role_list    = array();
  public $partner_list = array();
  public $updated      = array();
  public $dead_flag    = false;
  public $suicide_flag = false;
  public $revive_flag  = false;
  public $lost_flag    = false;

  public function __construct($role = null) {
    if (is_null($role)) return;
    $this->role = $role;
    $this->Parse();
  }

  //役職情報の展開処理
  public function Parse($role = null) {
    //初期化処理
    if (isset($role)) $this->role = $role;
    $this->partner_list = array();

    //展開用の正規表現をセット
    $regex_partner = '/([^\[]+)\[([^\]]+)\]/'; //恋人型 (role[id])
    $regex_status  = '/([^-]+)-(.+)/';         //憑依型 (role[date-id])

    //展開処理
    $role_list = array();
    foreach (explode(' ', $this->role) as $role) {
      if (preg_match($regex_partner, $role, $match_partner)) {
	$role_list[] = $match_partner[1];
	if (preg_match($regex_status, $match_partner[2], $match_status)) {
	  $this->partner_list[$match_partner[1]][$match_status[1]] = $match_status[2];
	}
	else {
	  $this->partner_list[$match_partner[1]][] = $match_partner[2];
	}
      }
      else {
	$role_list[] = $role;
      }
    }

    //代入処理
    $this->role_list = array_unique($role_list);
    $this->main_role = $this->role_list[0];
  }

  //役職の再パース処理
  public function Reparse() {
    $this->Parse($this->GetRole());
  }

  //役職再パース + Stack 処理
  public function StackReparse() {
    $role = $this->GetRole();
    if ($this->role == $role) return;
    //Text::p($role, "◆StackReparse [{$this->uname}]");
    $this->reparse = new self($role);
  }

  //player 入れ替え処理
  public function ChangePlayer($id) {
    if (! isset(DB::$USER->player) || ! isset($this->role_id) || $this->role_id == $id) {
      return false;
    }
    $this->role_id = $id;
    $this->Parse(DB::$USER->player->role_list[$id]);
    return true;
  }

  //夜の投票取得
  public function LoadVote($type, $not_type = '') {
    return UserDB::GetVote($this->id, $type, $not_type);
  }

  //仮想ユーザ取得
  public function GetVirtual() {
    return DB::$USER->ByVirtual($this->id);
  }

  //実ユーザ取得
  public function GetReal() {
    return DB::$USER->ByReal($this->id);
  }

  //再パースユーザ取得
  public function GetReparse() {
    return isset($this->reparse) ? $this->reparse : $this;
  }

  //ユーザ ID 取得
  public function GetID($role = null) {
    return isset($role) ? sprintf('%s[%d]', $role, $this->id) : $this->id;
  }

  //HN 取得
  public function GetName() {
    return $this->GetVirtual()->handle_name;
  }

  //役職取得
  public function GetRole() {
    return isset($this->updated['role']) ? $this->updated['role'] : $this->role;
  }

  //メイン役職取得
  public function GetMainRole($virtual = false) {
    return $virtual && isset($this->virtual_role) ? $this->virtual_role : $this->main_role;
  }

  //所属陣営取得
  public function GetCamp($win = false, $reparse = false) {
    $type = $win ? 'win_camp' : 'main_camp';
    if (! isset($this->$type)) DB::$USER->SetCamp($this, $type, $reparse);
    return $this->$type;
  }

  //拡張情報取得
  public function GetPartner($type, $fill = false) {
    $stack = isset($this->partner_list[$type]) ? $this->partner_list[$type] : null;
    return is_array($stack) ? $stack : ($fill ? array() : null);
  }

  //メイン役職の拡張情報取得
  public function GetMainRoleTarget() {
    return array_shift($this->GetPartner($this->main_role, true));
  }

  //日数に応じた憑依先の ID 取得
  public function GetPossessedTarget($type, $today) {
    if (is_null($stack = $this->GetPartner($type))) return false;

    $date_list = array_keys($stack);
    krsort($date_list);
    foreach ($date_list as $date) {
      if ($date <= $today) return $stack[$date];
    }
    return false;
  }

  //死の宣告系の宣告日取得
  public function GetDoomDate($role) {
    return max($this->GetPartner($role));
  }

  //周辺 ID を取得
  public function GetAround() {
    $max   = DB::$USER->GetUserCount();
    $num   = $this->id;
    $stack = array();
    for ($i = -1; $i < 2; $i++) {
      $j = $num + $i * 5;
      if ($j < 1 || $max + 1 < $j) continue;
      if ($j <= $max) $stack[] = $j;
      if (($j % 5) != 1 && $j > 1)    $stack[] = $j - 1;
      if (($j % 5) != 0 && $j < $max) $stack[] = $j + 1;
    }
    return $stack;
  }

  //役職クラスを取得
  public function GetFilter($role) {
    return RoleManager::GetClass($role);
  }

  //メイン役職クラスを取得
  public function GetMainFilter() {
    return $this->GetFilter($this->main_role);
  }

  //発言数を取得
  public function GetTalkCount($lock = false) {
    if (! isset($this->talk_count) || $lock) {
      $stack = TalkDB::GetUserTalkCount($lock);
      $this->talk_count = $stack['date'] == DB::$ROOM->date ? $stack['talk_count'] : 0;
    }
    return $this->talk_count;
  }

  //生存フラグ判定
  public function IsLive($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == UserLive::LIVE : ! $dead;
  }

  //死亡フラグ判定
  public function IsDead($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == UserLive::DEAD || $this->IsDrop() : $dead;
  }

  //蘇生辞退フラグ判定
  public function IsDrop() {
    return $this->live == UserLive::DROP;
  }

  //同一ユーザ判定
  public function IsSame(User $user) {
    return $this === $user;
  }

  //同一名判定
  public function IsSameName($uname) {
    return $this->uname == $uname;
  }

  //自分と同一ユーザ判定
  public function IsSelf() {
    return $this->IsSame(DB::$SELF);
  }

  //身代わり君判定
  public function IsDummyBoy($strict = false) {
    return $this->IsSameName(GM::DUMMY_BOY) && ! ($strict && DB::$ROOM->IsQuiz());
  }

  //役職判定
  public function IsRole($role) {
    $stack = func_get_args();
    $list  = $this->role_list;
    if ($stack[0] === true) { //仮想役職対応
      array_shift($stack);
      if (isset($this->virtual_role)) $list[] = $this->virtual_role;
    }
    if (is_array($stack[0])) $stack = $stack[0];

    if (count($stack) > 1) {
      return count(array_intersect($stack, $list)) > 0;
    } else {
      return in_array($stack[0], $list);
    }
  }

  //役職グループ判定
  public function IsRoleGroup($role) {
    $stack     = func_get_args();
    $role_list = $this->role_list;
    if ($stack[0] === true) { //仮想役職対応
      array_shift($stack);
      if (isset($this->virtual_role)) $role_list[] = $this->virtual_role;
    }
    if (is_array($stack[0])) $stack = $stack[0];

    foreach ($stack as $target) {
      foreach ($role_list as $role) {
	if (strpos($role, $target) !== false) return true;
      }
    }
    return false;
  }

  //生存 + 役職判定
  public function IsLiveRole($role, $strict = false) {
    return $this->IsLive($strict) && $this->IsRole($role);
  }

  //生存 + 役職グループ判定
  public function IsLiveRoleGroup($role) {
    return $this->IsLive(true) && $this->IsRoleGroup(func_get_args());
  }

  //同一陣営判定
  public function IsCamp($camp, $win = false) {
    return $this->GetCamp($win) == $camp;
  }

  //同一陣営判定 (メイン役職限定)
  public function IsMainCamp($camp) {
    return $this->DistinguishCamp() == $camp;
  }

  //同一役職系判定
  public function IsMainGroup($group) {
    $stack = func_get_args();
    return in_array($this->DistinguishRoleGroup(), $stack);
  }

  //拡張判定
  public function IsPartner($type, $target) {
    if (is_null($partner_list = $this->GetPartner($type))) return false;
    if (is_array($target)) {
      if (! array_key_exists($type, $target)) return false;
      if (! is_array($target_list = $target[$type])) return false;
      return count(array_intersect($partner_list, $target_list)) > 0;
    }
    else {
      return in_array($target, $partner_list);
    }
  }

  //能力喪失判定
  public function IsActive($role = null) {
    return (is_null($role) || $this->IsRole($role)) &&
      ! $this->lost_flag && ! $this->IsRole('lost_ability');
  }

  //孤立系役職判定
  public function IsLonely() {
    return $this->IsRole('mind_lonely') || $this->IsRoleGroup('silver');
  }

  //男性判定
  public function IsMale() {
    return $this->sex == Sex::MALE;
  }

  //女性判定
  public function IsFemale() {
    return $this->sex == Sex::FEMALE;
  }

  //共有者系判定
  public function IsCommon($talk = false) {
    return $this->IsMainGroup('common') && ! ($talk && $this->IsRole('dummy_common'));
  }

  //人狼系判定
  public function IsWolf($talk = false) {
    return $this->IsMainGroup('wolf') && ! ($talk && $this->IsLonely());
  }

  //覚醒天狼判定
  public function IsSiriusWolf($full = true) {
    if (! $this->IsRole('sirius_wolf')) return false;
    $type = $full ? 'ability_full_sirius_wolf' : 'ability_sirius_wolf';
    if (! isset($this->$type)) {
      $stack = DB::$USER->GetLivingWolves();
      $this->ability_sirius_wolf      = count($stack) < 3;
      $this->ability_full_sirius_wolf = count($stack) == 1;
    }
    return $this->$type;
  }

  //妖狐判定
  public function IsFox($talk = false) {
    return $this->IsFoxCount() && ! ($talk && ($this->IsChildFox() || $this->IsLonely()));
  }

  //妖狐カウント判定
  public function IsFoxCount() {
    return $this->IsMainGroup('fox', 'child_fox');
  }

  //子狐系判定
  public function IsChildFox($vote = false) {
    if ($vote) {
      return $this->IsRole(RoleFilterData::$vote_child_fox);
    } else {
      return $this->IsMainGroup('child_fox');
    }
  }

  //鬼陣営判定
  public function IsOgre() {
    return $this->IsMainCamp('ogre');
  }

  //時間差コピー能力者判定
  public function IsDelayMania() {
    return $this->IsRole('soul_mania', 'dummy_mania');
  }

  //鵺系判定
  public function IsUnknownMania() {
    return $this->IsMainGroup('unknown_mania');
  }

  //恋人判定
  public function IsLovers($fake = false) {
    $stack = array('lovers');
    if ($fake) $stack[] = 'fake_lovers';
    return $this->IsRole($stack);
  }

  //難題耐性判定
  public function IsChallengeLovers() {
    return 1 < DB::$ROOM->date && DB::$ROOM->date < 5 && $this->IsRole('challenge_lovers');
  }

  //特殊恋人耐性判定
  public function IsAvoidLovers($strict = false) {
    return (! $strict && $this->IsChallengeLovers()) || $this->IsRole('vega_lovers');
  }

  //ジョーカー所持者判定
  public function IsJoker($shift = false) {
    if (! $this->IsRole('joker')) return false;
    return RoleManager::GetClass('joker')->IsJoker($this, $shift);
  }

  //期間限定表示役職
  public function IsDoomRole($role) {
    return $this->IsRole($role) && $this->GetDoomDate($role) == DB::$ROOM->date;
  }

  //護衛成功済み判定
  public function IsFirstGuardSuccess($id) {
    $flag = ! (isset($this->guard_success) && in_array($id, $this->guard_success));
    $this->guard_success[] = $id;
    return $flag;
  }

  //毒能力の発動判定
  public function IsPoison() {
    if (DB::$ROOM->IsEvent('no_poison') || ! $this->IsRoleGroup('poison')) return false; //無効判定
    return $this->GetMainFilter()->IsPoison();
  }

  //蘇生能力者判定
  public function IsReviveGroup($active = false) {
    return ($this->IsMainGroup('poison_cat') ||
	    $this->IsRole('revive_medium', 'revive_doll_master', 'revive_fox')) &&
      ! ($active && ! $this->IsActive());
  }

  //蘇生制限判定
  public function IsReviveLimited() {
    return $this->IsDrop() || (isset($this->possessed_reset) && $this->possessed_reset) ||
      $this->IsLovers() || $this->IsMainGroup('poison_cat', 'depraver') ||
      $this->IsRoleGroup('revive') || $this->IsDelayMania() ||
      $this->IsRole(RoleFilterData::$revive_limited);
  }

  //暗殺反射判定
  public function IsReflectAssassin() {
    if (DB::$ROOM->IsEvent('no_reflect_assassin') || $this->IsDead(true)) return false; //無効判定

    //常時反射
    if ($this->IsRole(RoleFilterData::$reflect_assassin) ||
	$this->IsSiriusWolf(false) || $this->IsAvoidLovers()) {
      return true;
    }

    //確率反射
    if ($this->IsRole('cursed_brownie')) {
      $rate = 30;
    }
    elseif ($this->IsMainCamp('ogre')) {
      //天候判定
      if (DB::$ROOM->IsEvent('full_ogre')) return true;
      if (DB::$ROOM->IsEvent('seal_ogre')) return false;
      $rate = RoleManager::GetClass($this->main_role)->reflect_rate;
    }
    else {
      $rate = 0;
    }

    if (DB::$ROOM->IsEvent('boost_reflect')) $rate += 30; //天候補正
    //Text::p($rate, sprintf('◆rate / %s [reflect]', $this->uname));
    if ($rate < 1) return false;

    return $rate >= 100 || Lottery::Percent($rate);
  }

  //憑依能力者判定 (被憑依者とコード上で区別するための関数)
  public function IsPossessedGroup() {
    return $this->IsRole(RoleFilterData::$possessed_group);
  }

  //憑依制限判定
  public function IsPossessedLimited() {
    return $this->IsPossessedGroup() || $this->IsRole(RoleFilterData::$possessed_limited);
  }

  //呪返し判定
  public function IsCursed() {
    return ! DB::$ROOM->IsEvent('no_cursed') && $this->IsLive(true) && $this->IsRoleGroup('cursed');
  }

  //嘘つき判定
  public function IsLiar() {
    return $this->DistinguishLiar() == 'psycho_mage_liar';
  }

  //遺言制限判定
  public function IsLastWordsLimited($save = false) {
    $stack = RoleFilterData::$last_words_limited;
    if ($save) {
      array_push($stack, 'possessed_exchange', 'letter_exchange');
    }
    return $this->IsMainGroup('escaper') || $this->IsRole($stack);
  }

  //特殊耐性判定
  public function IsAvoid($quiz = false) {
    $stack = array('detective_common');
    if ($quiz) $stack[] = 'quiz';
    return $this->IsRole($stack) || $this->IsSiriusWolf() || $this->IsAvoidLovers();
  }

  //毒回避判定
  public function IsAvoidPoison() {
    return $this->IsRole(RoleFilterData::$avoid_poison) || $this->IsAvoid(true);
  }

  //人外カウント判定
  public function IsInhuman() {
    return $this->IsWolf() || $this->IsFox();
  }

  //所属陣営判別 (ラッパー)
  public function DistinguishCamp() {
    return RoleData::GetCamp($this->main_role);
  }

  //所属役職グループ陣営判別 (ラッパー)
  public function DistinguishRoleGroup() {
    return RoleData::GetGroup($this->main_role);
  }

  //精神鑑定
  public function DistinguishLiar() {
    return RoleManager::GetClass('psycho_mage')->DistinguishLiar($this);
  }

  //有効シーン判定
  public function CheckScene() {
    return $this->last_load_scene == DB::$ROOM->scene;
  }

  //投票済み判定
  public function ExistsVote() {
    if (DB::$ROOM->IsBeforeGame()) {
      return DB::$ROOM->Stack()->ExistsArray('vote', $this->id);
    } else {
      return DB::$ROOM->Stack()->ExistsKey('vote', $this->id);
    }
  }

  //未投票チェック
  public function CheckVote(array $list) {
    if ($this->IsDummyBoy() || $this->IsDead()) return true;
    if ($this->IsDoomRole('death_note')) {
      if (! ((isset($list['DEATH_NOTE_NOT_DO']) &&
	      array_key_exists($this->id, $list['DEATH_NOTE_NOT_DO'])) ||
	     isset($list['DEATH_NOTE_DO'][$this->id]))) return false;
    }
    return RoleManager::LoadMain($this)->IsFinishVote($list);
  }

  //役職情報から表示情報を作成する
  public function GenerateRoleName($main_only = false) {
    $str = RoleDataHTML::Generate($this->main_role); //メイン役職
    if ($main_only) return $str;

    if (($role_count = count($this->role_list)) < 2) return $str; //サブ役職
    $count = 1;
    foreach (RoleData::$sub_role_group_list as $class => $role_list) {
      foreach ($role_list as $sub_role) {
	if (! $this->IsRole($sub_role)) continue;
	switch ($sub_role) {
	case 'joker':
	  $css = $this->IsJoker() ? $class : 'chiroptera';
	  break;

	case 'death_note':
	  $css = $this->IsDoomRole($sub_role) ? $class : 'chiroptera';
	  break;

	default:
	  $css = $class;
	  break;
	}
	$str .= RoleDataHTML::Generate($sub_role, $css, true);
	if (++$count >= $role_count) break 2;
      }
    }
    return $str;
  }

  //役職をパースして省略名を返す
  public function GenerateShortRoleName($heaven = false, $main_only = false) {
    if (empty($this->main_role)) return;
    if (isset($this->role_id)) { //キャッシュ判定
      if ($main_only && isset(DB::$USER->short_role_main[$this->role_id])) {
	return DB::$USER->short_role_main[$this->role_id];
      }
      elseif (isset(DB::$USER->short_role[$this->role_id])) {
	return DB::$USER->short_role[$this->role_id];
      }
    }

    //メイン役職を取得
    $camp = $this->GetCamp();
    $name = RoleData::GetShortName($this->main_role);
    $str  = '<span class="add-role"> [';
    $str .= $camp == 'human' ? $name : sprintf('<span class="%s">%s</span>', $camp, $name);
    if ($main_only) {
      $str = $this->handle_name . $str . ']</span>';
      if (isset($this->role_id)) DB::$USER->short_role_main[$this->role_id] = $str;
      return $str;
    }

    //サブ役職を追加
    foreach (RoleData::GetShortDiff($this->role_list) as $role => $name) {
      switch ($role) {
      case 'lovers':
      case 'challenge_lovers':
      case 'vega_lovers':
      case 'fake_lovers':
      case 'possessed_exchange':
      case 'letter_exchange':
	$str .= sprintf('<span class="%s">%s</span>', 'lovers', $name);
	break;

      case 'infected':
      case 'psycho_infected':
	$str .= sprintf('<span class="%s">%s</span>', 'vampire', $name);
	break;

      case 'rival':
      case 'enemy':
      case 'supported':
	$str .= sprintf('<span class="%s">%s</span>', 'duelist', $name);
	break;

      default:
	$str .= $name;
	break;
      }
    }
    $uname = $heaven ? $this->uname : DB::$USER->TraceExchange($this->id)->uname;
    $str .= '] (' . $uname . ')</span>';
    if (isset($this->role_id) && ! $this->IsRole('possessed_exchange')) {
      DB::$USER->short_role[$this->role_id] = $str;
    }
    return $str;
  }

  //投票画面用アイコンタグ生成
  public function GenerateVoteTag($icon_path, $checkbox) {
    $tag = Icon::GetTag();
    return <<<EOF
<td><label for="{$this->id}">
<img src="{$icon_path}" style="border-color: {$this->color};" {$tag}>
<font color="{$this->color}">◆</font>{$this->handle_name}<br>
{$checkbox}</label></td>

EOF;
  }

  //発言数初期化処理
  public function InitializeTalkCount() {
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%d: %s', $this->id, $this->uname), '◆Initialize Talk Count');
      return;
    }

    $items  = 'room_no, user_no, date, talk_count';
    $values = sprintf("'%d', '%d', '%d', '%d'", DB::$ROOM->id, $this->id, DB::$ROOM->date, 0);
    return DB::Insert('user_talk_count', $items, $values);
  }

  //個別 DB 更新処理
  public function Update($item, $value) {
    if (DB::$ROOM->IsTest()) {
      if (is_null($value)) $value = 'NULL (reset)';
      Text::p($value, sprintf('Change [%s] (%s)', $item, $this->uname));
      return true;
    }
    $set = sprintf('%s = %s', $item, is_null($value) ? 'NULL' : "'{$value}'");
    return UserDB::Update($set, array(), $this->id);
  }

  //更新処理
  public function UpdateList(array $list) {
    $stack     = array();
    $set_stack = array();
    foreach ($list as $key => $value) {
      $set_stack[] = sprintf('%s = ?', $key);
      $stack[] = $value;
    }
    return UserDB::Update(implode(',', $set_stack), $stack, $this->id);
  }

  //生存情報更新処理
  public function UpdateLive($live) {
    return $this->Update('live', $live);
  }

  //ID 更新処理 (KICK 後処理用)
  public function UpdateID($id) {
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%d -> %d: %s', $this->id, $id, $this->uname), 'Change ID');
      return;
    }
    return UserDB::UpdateID($id, $this->uname);
  }

  //player 更新処理
  public function UpdatePlayer() {
    if (! isset($this->updated['role'])) return true;
    $role = $this->updated['role'];
    if (DB::$ROOM->IsTest()) {
      Text::p($role, sprintf('Player (%s)', $this->uname));
      return true;
    }
    $items  = 'room_no, date, scene, user_no, role';
    $values = sprintf("%d, %d, '%s', %d, '%s'",
		      DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, $this->id, $role);
    if (! DB::Insert('player', $items, $values)) return false;
    return $this->Update('role_id', DB::GetInsertID());
  }

  //基幹死亡処理
  public function ToDead() {
    if ($this->IsDead(true)) return false;
    $this->UpdateLive(UserLive::DEAD);
    $this->dead_flag = true;
    return true;
  }

  //蘇生処理
  public function Revive($virtual = false) {
    if ($this->IsLive(true)) return false;
    $this->UpdateLive(UserLive::LIVE);
    $this->revive_flag = true;
    if (! $virtual) DB::$ROOM->ResultDead($this->handle_name, 'REVIVE_SUCCESS');
    return true;
  }

  //役職更新処理
  public function ChangeRole($role) {
    $this->Update('role', $role);
    $this->updated['role'] = $role; //キャッシュ本体の更新は行わない
  }

  //役職置換処理
  public function ReplaceRole($target, $replace) {
    $this->ChangeRole(str_replace($target, $replace, $this->GetRole()));
  }

  //役職追加処理
  public function AddRole($role) {
    $base_role = $this->GetRole();
    if (in_array($role, explode(' ', $base_role))) return false; //同じ役職は追加しない
    $this->ChangeRole($base_role . ' ' . $role);
  }

  //仮想役職追加処理 (キャッシュ限定)
  public function AddVirtualRole($role) {
    if (! in_array($role, $this->role_list)) $this->role_list[] = $role;
  }

  //メイン役職追加処理
  public function AddMainRole($role) {
    $this->ReplaceRole($this->main_role, $this->main_role . '[' . $role . ']');
  }

  //死の宣告処理
  public function AddDoom($date, $role = 'death_warrant') {
    $this->AddRole(sprintf('%s[%d]', $role, DB::$ROOM->date + $date));
  }

  //能力喪失処理
  public function LostAbility() {
    $this->AddRole('lost_ability');
    $this->lost_flag = true;
  }

  //憑依解除処理
  public function ReturnPossessed($type) {
    $this->AddRole(sprintf('%s[%d-%d]', $type, DB::$ROOM->date + 1, $this->id));
  }

  //遺言を取得して保存する
  public function SaveLastWords($handle_name = null) {
    if (! $this->IsDummyBoy() && $this->IsLastWordsLimited(true)) return true; //スキップ判定
    if (is_null($handle_name)) $handle_name = $this->handle_name;
    if (DB::$ROOM->IsTest()) {
      Text::p(sprintf('%s (%s)', $handle_name, $this->uname), 'LastWords');
      return true;
    }

    if (is_null($message = UserDB::GetLastWords($this->id))) return true;

    $items  = 'room_no, date, handle_name, message';
    $values = sprintf("%d, %d, '%s', '%s'", DB::$ROOM->id, DB::$ROOM->date, $handle_name, $message);
    return DB::Insert('result_lastwords', $items, $values);
  }

  //投票処理
  public function Vote($action, $target = null, $vote_number = null) {
    if (DB::$ROOM->IsTest()) {
      if (DB::$ROOM->IsDay()) {
	$stack = array('user_no'   => $this->id, 'uname'       => $this->uname,
		       'target_no' => $target,   'vote_number' => $vote_number);
	RQ::GetTest()->vote->day[$this->uname] = $stack;
	//Text::p($stack, '◆Vote');
      } else {
	Text::p(sprintf('%s: %s: %s', $action, $this->uname, $target), 'Vote');
      }
      return true;
    }
    $items = 'room_no, date, scene, type, uname, user_no, vote_count';
    $values = sprintf("%d, %d, '%s', '%s', '%s', %d, %d",
		      DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, $action,
		      $this->uname, $this->id, DB::$ROOM->vote_count);
    if (isset($target)) {
      $items  .= ', target_no';
      $values .= sprintf(", '%s'", $target);
    }
    if (isset($vote_number)) {
      $items  .= ', vote_number, revote_count';
      $values .= sprintf(', %d, %d', $vote_number, RQ::Get()->revote_count);
    }
    return DB::Insert('vote', $items, $values);
  }

  //デバッグ用
  public function p($data = null, $name = null) {
    Text::p(is_null($data) ? $this : $this->$data, $name);
  }

  //仮想的な生死判定
  private function IsDeadFlag($strict = false) {
    if (! $strict) return null;
    if ($this->suicide_flag) return true;
    if ($this->revive_flag)  return false;
    if ($this->dead_flag)    return true;
    return null;
  }
}

//-- ユーザ情報ローダー --//
class UserData {
  public $room_no;
  public $rows = array();
  public $kick = array();
  public $name = array();
  public $role = array();

  public function __construct(RequestBase $request, $lock = false) {
    $this->room_no = $request->room_no;
    $this->Load($request, $lock);
  }

  //ユーザ名 -> ユーザ ID 変換
  public function UnameToNumber($uname) {
    return array_key_exists($uname, $this->name) ? $this->name[$uname] : null;
  }

  //ユーザ情報取得 (ユーザ ID 経由)
  public function ByID($id) {
    if (is_null($id)) return new User();
    $stack = $this->{ $id > 0 ? 'rows' : 'kick' };
    return array_key_exists($id, $stack) ? $stack[$id] : new User();
  }

  //ユーザ情報取得 (ユーザ名経由)
  public function ByUname($uname) {
    return $this->ByID($this->UnameToNumber($uname));
  }

  //ユーザ情報取得 (クッキー経由)
  public function BySession() {
    return $this->TraceExchange(Session::GetUser());
  }

  //ユーザ情報取得 (憑依先ユーザ ID 経由)
  public function ByVirtual($id) {
    return $this->TraceVirtual($id, 'possessed_target');
  }

  //ユーザ情報取得 (憑依元ユーザ ID 経由)
  public function ByReal($id) {
    return $this->TraceVirtual($id, 'possessed');
  }

  //ユーザ情報取得 (憑依先ユーザ名経由)
  public function ByVirtualUname($uname) {
    return $this->ByVirtual($this->UnameToNumber($uname));
  }

  //ユーザ情報取得 (憑依元ユーザ名経由)
  public function ByRealUname($uname) {
    return $this->ByReal($this->UnameToNumber($uname));
  }

  //交換憑依情報追跡
  public function TraceExchange($id) {
    $user = $this->ByID($id);
    $role = 'possessed_exchange';
    if (! $user->IsRole($role) || ! DB::$ROOM->IsPlaying() ||
	(DB::$ROOM->IsOff('log') && $user->IsDead())) {
      return $user;
    }

    $stack = $user->GetPartner($role);
    return is_array($stack) && DB::$ROOM->date > 2 ? $this->ByID(array_shift($stack)) : $user;
  }

  //HN 取得
  public function GetHandleName($uname, $virtual = false) {
    $user = $virtual ? $this->ByVirtualUname($uname) : $this->ByUname($uname);
    return property_exists($user, 'handle_name') ? $user->handle_name : '';
  }

  //身代わり君 ID 取得 (現状は固定値)
  public function GetDummyBoyID() {
    return GM::ID;
  }

  //ユーザ数カウント
  public function GetUserCount() {
    return count($this->rows);
  }

  //全ユーザ数カウント
  public function GetAllUserCount() {
    return count($this->name);
  }

  //生存者を取得する
  public function GetLivingUsers($strict = false) {
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsLive($strict)) $stack[$user->id] = $user->uname;
    }
    return $stack;
  }

  //生存している人狼を取得する
  public function GetLivingWolves() {
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsLive() && $user->IsWolf()) $stack[] = $user->id;
    }
    return $stack;
  }

  //出現した妖狐カウントを取得する
  public function GetFoxCount() {
    $count = 0;
    foreach ($this->rows as $user) {
      if ($user->IsFoxCount()) $count++;
    }
    return $count;
  }

  //所属陣営を判定してキャッシュする
  public function SetCamp(User $user, $type, $reparse = false) {
    if ($type == 'win_camp' && $user->IsLovers(true)) {
      $user->$type = 'lovers';
      return;
    }

    $target = $user;
    $stack  = array();
    while ($target->IsUnknownMania()) { //鵺系ならコピー先を辿る
      $id = $target->GetMainRoleTarget();
      if (is_null($id) || in_array($id, $stack)) break;
      $stack[] = $id;
      $target  = $reparse ? $this->ByID($id)->GetReparse() : $this->ByID($id);
    }

    //時間差コピー能力者ならコピー先を辿る
    if ($target->IsDelayMania() && ! is_null($id = $target->GetMainRoleTarget())) {
      $target = $reparse ? $this->ByID($id)->GetReparse() : $this->ByID($id);
      if ($target->IsRoleGroup('mania')) $target = $user; //神話マニア系なら元に戻す
    }
    $user->$type = $target->DistinguishCamp();
  }

  //特殊イベント情報セット
  public function SetEvent($force = false) {
    if (DB::$ROOM->id < 1 || ! is_array($event_list = DB::$ROOM->GetEvent($force))) return;
    $stack = DB::$ROOM->Stack()->Get('event');
    //Text::p($event_list, '◆Event [row]');
    foreach ($event_list as $event) {
      switch ($event['type']) {
      case 'EVENT':
	$stack->On($event['message']);
	break;

      case 'BLIND_VOTE':
	$date = DB::$ROOM->date - (DB::$ROOM->IsDay() ? 1 : 0);
	$stack->Set('blind_vote', $date == $event['message']);
	break;

      case 'SAME_FACE':
	$stack->On('same_face');
	DB::$ROOM->Stack()->Set('same_face', $event['message']);
	break;

      case 'WEATHER':
	$id = (int)$event['message'];
	$stack->On(WeatherData::GetEvent($id));
	DB::$ROOM->Stack()->Set('weather', $id);
	break;

      case 'VOTE_DUEL':
	RoleManager::LoadMain($this->ByID($event['message']))->SetEvent($this);
	break;
      }
    }

    if (DB::$ROOM->IsEvent('hyper_critical')) {
      $stack->On('critical_voter');
      $stack->On('critical_luck');
    }
    elseif (DB::$ROOM->IsEvent('aurora')) {
      $stack->On('blinder');
      $stack->On('mind_open');
    }
    //DB::$ROOM->Stack()->p('event', '◆EventStack');

    if (DB::$ROOM->IsDay()) { //昼限定
      foreach (RoleFilterData::$event_virtual_day as $role) {
	if (DB::$ROOM->IsEvent($role)) {
	  foreach ($this->rows as $user) $user->AddVirtualRole($role);
	}
      }
    }

    if (DB::$ROOM->IsPlaying()) { //昼夜両方
      foreach (RoleFilterData::$event_virtual as $role) {
	if (DB::$ROOM->IsEvent($role)) {
	  foreach ($this->rows as $user) $user->AddVirtualRole($role);
	}
      }

      foreach (RoleFilterData::$event_bad_status as $role) {
	if ($this->IsAppear($role)) {
	  RoleManager::GetClass($role)->BadStatus($this);
	}
      }
    }
  }

  //霊界の配役公開判定
  public function IsOpenCast() {
    $evoke_scanner = array();
    $mind_evoke    = array();
    foreach ($this->rows as $user) {
      if ($user->IsDummyBoy()) continue;

      if ($user->IsReviveGroup(true) || $user->IsRole('revive_mania')) {
	if ($user->IsLive()) return false;
      }
      elseif ($user->IsRole('revive_priest')) {
	if ($user->IsActive()) return false;
      }
      elseif ($user->IsRole('evoke_scanner')) {
	if ($user->IsLive()) {
	  if (DB::$ROOM->IsDate(1)) return false;
	  $evoke_scanner[] = $user->id;
	}
      }
      elseif ($user->IsDelayMania()) {
	if (DB::$ROOM->IsDate(1) || ! is_null($user->GetMainRoleTarget())) return false;
      }

      if ($user->IsRole('mind_evoke')) {
	$mind_evoke = array_merge($mind_evoke, $user->GetPartner('mind_evoke'));
      }
    }
    return count(array_intersect($evoke_scanner, $mind_evoke)) < 1;
  }

  //仮想的な生死を返す
  public function IsVirtualLive($id, $strict = false) {
    //憑依されている場合は憑依者の生死を返す
    $real_user = $this->ByReal($id);
    if ($real_user->id != $id) return $real_user->IsLive($strict);

    //憑依先に移動している場合は常に死亡扱い
    if ($this->ByVirtual($id)->id != $id) return false;

    //憑依が無ければ本人の生死を返す
    return $this->ByID($id)->IsLive($strict);
  }

  //死亡処理
  public function Kill($id, $reason, $type = null) {
    $user = $this->ByReal($id);
    if (! $user->ToDead()) return false;

    $virtual_user = $this->ByVirtual($user->id);
    DB::$ROOM->ResultDead($virtual_user->handle_name, $reason, $type);

    switch ($reason) {
    case 'NOVOTED':
    case 'SILENCE':
    case 'POSSESSED_TARGETED':
      return true;

    default: //遺言処理
      $user->SaveLastWords($virtual_user->handle_name);
      if (! $virtual_user->IsSame($user)) $virtual_user->SaveLastWords();
      return true;
    }
  }

  //突然死処理
  public function SuddenDeath($id, $reason, $type = null) {
    if (! $this->Kill($id, $reason, $type)) return false;

    $user = $this->ByReal($id);
    $user->suicide_flag = true;

    switch ($reason) {
    case 'NOVOTED':
    case 'SILENCE':
      $str = strtolower($reason);
      break;

    default:
      $str ='sudden_death';
      break;
    }

    DB::$ROOM->Talk($user->GetName() . ' ' . DeadMessage::$$str);
    return true;
  }

  //-- 役職関連 --//
  //希望役職取得
  public function GetWishRole($uname) {
    return $this->ByUname($uname)->role;
  }

  //役職ユーザ ID 取得
  public function GetRoleID($role) {
    return isset($this->role[$role]) ? $this->role[$role] : array();
  }

  //役職ユーザ数取得
  public function GetRoleCount($role) {
    return count($this->GetRoleID($role));
  }

  //役職ユーザ取得
  public function GetRoleUser($role) {
    $stack = array();
    foreach ($this->GetRoleID($role) as $id) {
      $stack[] = $this->ByID($id);
    }
    return $stack;
  }

  //役職の出現判定
  public function IsAppear($role) {
    $role_list = func_get_args();
    return count(array_intersect($role_list, array_keys($this->role))) > 0;
  }

  //役職の生存判定
  public function IsLiveRole($role) {
    if (! $this->IsAppear($role)) return false; //存在判定
    foreach ($this->GetRoleUser($role) as $user) {
      if ($user->IsLive(true)) return true;
    }
    return false;
  }

  //-- ログ処理用 --//
  //仮想役職リストの保存
  public function SaveRoleList() {
    foreach ($this->rows as $user) {
      $user->save_role_list = $user->role_list;
    }
  }

  //仮想役職リストの初期化
  public function ResetRoleList() {
    foreach ($this->rows as $user) {
      $user->role_list = $user->save_role_list;
    }
  }

  //player の復元
  public function ResetPlayer() {
    if (! isset($this->player->user_list)) return;
    foreach ($this->player->user_list as $id => $stack) {
      $this->ByID($id)->ChangePlayer(max($stack));
    }
  }

  //-- 投票処理用 --//
  //KICK の後処理
  public function UpdateKick() {
    $id = 1;
    foreach ($this->rows as $user) {
      if ($user->id != $id) {
	$user->UpdateID($id);
	$user->id = $id;
      }
      $id++;
    }
    foreach ($this->kick as $user) $user->UpdateID(-1);
  }

  //ゲーム開始処理
  public function GameStart($init_talk) {
    foreach ($this->rows as $user) {
      $user->UpdatePlayer();
      if ($init_talk) $user->InitializeTalkCount();
    }
  }

  //-- private --//
  //村情報のロード処理
  private function Load(RequestBase $request, $lock = false) {
    if ($request->IsVirtualRoom()) { //仮想モード
      $user_list = $request->GetTest()->test_users;
    }
    elseif (isset($request->retrieve_type)) { //特殊モード
      switch ($request->retrieve_type) {
      case 'entry_user': //入村処理
	$user_list = UserDataDB::LoadEntryUser($request->room_no);
	break;

      case RoomScene::BEFORE: //ゲーム開始前
	$user_list = UserDataDB::LoadBeforegame($request->room_no);
	break;

      case RoomScene::DAY: //昼 + 下界
	$user_list = UserDataDB::LoadDay($request->room_no);
	break;
      }
    }
    else {
      $user_list = UserDataDB::Load($request->room_no, $lock);
    }
    if (class_exists('RoleManager')) RoleManager::LoadStack();
    $this->Parse($user_list);
  }

  //ユーザ情報を User クラスでパースして登録
  private function Parse(array $user_list) {
    //初期化処理
    $this->rows = array();
    $this->kick = array();
    $this->name = array();
    $this->role = array();
    $kick = 0;

    foreach ($user_list as $user) {
      $user->Parse();
      if ($user->id >= 0 && $user->live != UserLive::KICK) { //KICK 判定
	$this->rows[$user->id] = $user;
	foreach ($user->role_list as $role) {
	  if (! empty($role)) $this->role[$role][] = $user->id;
	}
      }
      else {
	$this->kick[$user->id = --$kick] = $user;
      }
      $this->name[$user->uname] = $user->id;
    }
    if (DB::$ROOM->IsOff('log')) $this->SetEvent();
    return count($this->name);
  }

  //憑依情報追跡
  private function TraceVirtual($id, $type) {
    $user = $this->ByID($id);
    if (! DB::$ROOM->IsPlaying()) return $user;
    switch ($type) {
    case 'possessed':
      if (! $user->IsRole($type)) return $user;
      break;

    default:
      if (! $user->IsPossessedGroup()) return $user;
      break;
    }

    $target_id = $user->GetPossessedTarget($type, DB::$ROOM->date);
    return $target_id === false ? $user : $this->ByID($target_id);
  }
}

//-- データベースアクセス (User 拡張) --//
class UserDB {
  /* user_entry */
  //ユーザクラス取得
  static function Load($user_no) {
    $query = <<<EOF
SELECT user_no AS id, uname, handle_name, sex, profile, role, icon_no, u.session_id,
  color, icon_name
FROM user_entry AS u INNER JOIN user_icon USING (icon_no)
WHERE room_no = ? AND user_no = ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, $user_no));
    return DB::FetchClass('User', true);
  }

  //ユーザ情報取得
  static function Get() {
    $query = 'SELECT * FROM user_entry WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, RQ::Get()->user_no));
    return DB::FetchAssoc(true);
  }

  //遺言取得
  static function GetLastWords($user_no) {
    $query = 'SELECT last_words FROM user_entry WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(DB::$ROOM->id, $user_no));
    return DB::FetchResult();
  }

  //キック済み判定
  static function IsKick($uname) {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND uname = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::KICK, $uname));
    return DB::Exists();
  }

  //重複ユーザ判定
  static function IsDuplicate($uname, $handle_name) {
    $query = <<<EOF
SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND (uname = ? OR handle_name = ?)
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::LIVE, $uname, $handle_name));
    return DB::Exists();
  }

  //重複 HN 判定
  static function IsDuplicateName($user_no, $handle_name) {
    $query = <<<EOF
SELECT user_no FROM user_entry WHERE room_no = ? AND user_no <> ? AND live = ? AND handle_name = ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, $user_no, UserLive::LIVE, $handle_name));
    return DB::Exists();
  }

  //重複 IP 判定
  static function IsDuplicateIP() {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND ip_address = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::LIVE, Security::GetIP()));
    return DB::Exists();
  }

  //ユーザ登録処理
  static function Insert(array $list) {
    extract($list);
    $crypt_password = Text::Crypt($password);
    $items  = 'room_no, user_no, uname, handle_name, icon_no, sex, password, live';
    $values = "{$room_no}, {$user_no}, '{$uname}', '{$handle_name}', {$icon_no}, '{$sex}', " .
      "'{$crypt_password}', 'live'";

    if ($uname != GM::DUMMY_BOY) {
      $session_id      = Session::GetUniqID();
      $ip_address      = Security::GetIP();
      $last_load_scene = RoomScene::BEFORE;
    }

    $stack = array('profile', 'role', 'session_id', 'last_words', 'ip_address', 'last_load_scene');
    foreach ($stack as $value) {
      if (isset($$value)) {
	$items  .= ", {$value}";
	$values .= ", '{$$value}'";
      }
    }
    return DB::Insert('user_entry', $items, $values);
  }

  //更新処理 (汎用)
  static function Update($set, array $list, $id) {
    $query = sprintf('UPDATE user_entry SET %s WHERE room_no = ? AND user_no = ?', $set);
    array_push($list, DB::$ROOM->id, $id);
    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  //更新処理 (ID 専用)
  static function UpdateID($id, $uname) {
    $query = 'UPDATE user_entry SET user_no = ? WHERE room_no = ? AND uname = ?';
    DB::Prepare($query, array($id, DB::$ROOM->id, $uname));
    return DB::FetchBool();
  }

  //キック処理
  static function Kick($id) {
    $query = 'UPDATE user_entry SET live = ?, session_id = NULL WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(UserLive::KICK, DB::$ROOM->id, $id));
    return DB::FetchBool();
  }

  /* vote */
  //投票取得
  static function GetVote($user_no, $type, $not_type) {
    $query = <<<EOF
SELECT type, target_no FROM vote WHERE room_no = ? AND date = ? AND vote_count = ? AND 
EOF;
    $list = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count);
    if ($type == 'WOLF_EAT' || $type == 'STEP_WOLF_EAT') {
      $query .= 'type IN (?, ?, ?)';
      array_push($list, 'WOLF_EAT', 'STEP_WOLF_EAT', 'SILENT_WOLF_EAT');
    }
    elseif ($not_type != '') {
      $query .= 'user_no = ? AND type IN (?, ?)';
      array_push($list, $user_no, $type, $not_type);
    }
    else {
      $query .= 'user_no = ? AND type = ?';
      array_push($list, $user_no, $type);
    }

    DB::Prepare($query, $list);
    return DB::FetchAssoc(true);
  }

  //処刑投票済み判定
  static function IsVoteKill() {
    //シーン進行の仕様上、この関数をコールした時点では同日投票データは処刑しか存在しない
    $query = <<<EOF
SELECT user_no FROM vote WHERE room_no = ? AND date = ? AND vote_count = ? AND user_no = ?
EOF;
    $list = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count, DB::$SELF->id);
    DB::Prepare($query, $list);
    return DB::Exists();
  }
}

//-- データベースアクセス (UserData 拡張) --//
class UserDataDB {
  //ユーザデータ取得
  static function Load($room_no, $lock = false) {
    $query = <<<EOF
SELECT room_no, user_no AS id, uname, handle_name, profile, sex, role, role_id, objection,
  live, last_load_scene, icon_filename, color
FROM user_entry LEFT JOIN user_icon USING (icon_no)
WHERE room_no = ? ORDER BY id ASC
EOF;
    if ($lock) $query .= ' FOR UPDATE';
    DB::Prepare($query, array($room_no));
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (入村処理用)
  static function LoadEntryUser($room_no) {
    $query = <<<EOF
SELECT room_no, user_no AS id, uname, handle_name, live, ip_address FROM user_entry
WHERE room_no = ? ORDER BY id ASC FOR UPDATE
EOF;
    DB::Prepare($query, array($room_no));
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (ゲーム開始前)
  static function LoadBeforegame($room_no) {
    if ($room_no != DB::$ROOM->id) return null;
    $query = <<<EOF
SELECT u.room_no, u.user_no AS id, u.uname, handle_name, profile, sex, role, role_id, objection,
  live, last_load_scene, icon_filename, color, v.type AS vote_type
FROM user_entry AS u LEFT JOIN user_icon USING (icon_no) LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.vote_count = ? AND u.user_no = v.user_no AND v.type = ?
WHERE u.room_no = ? ORDER BY id ASC
EOF;
    DB::Prepare($query, array(DB::$ROOM->vote_count, 'GAMESTART', DB::$ROOM->id));
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (昼 + 下界)
  static function LoadDay($room_no) {
    if ($room_no != DB::$ROOM->id) return null;
    $query = <<<EOF
SELECT u.room_no, u.user_no AS id, u.uname, handle_name, profile, sex, role, role_id, objection,
  live, last_load_scene, icon_filename, color, v.target_no AS target_no
FROM user_entry AS u LEFT JOIN user_icon USING (icon_no) LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.date = ? AND v.vote_count = ? AND
  u.user_no = v.user_no AND v.type = ?
WHERE u.room_no = ? ORDER BY id ASC
EOF;
    $list = array(DB::$ROOM->date, DB::$ROOM->vote_count, 'VOTE_KILL', DB::$ROOM->id);
    DB::Prepare($query, $list);
    return DB::FetchClass('User');
  }

  //生存陣営カウント
  static function CountCamp($type) {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND user_no > ? AND ';
    $list  = array(DB::$ROOM->id, UserLive::LIVE, 0);

    switch ($type) {
    case 'human':
      $query .= '!(role LIKE ?) AND !(role LIKE ?)';
      array_push($list, '%wolf%', '%fox%');
      break;

    case 'wolf':
      $query .= 'role LIKE ?';
      $list[] = '%wolf%';
      break;

    case 'fox':
      $query .= 'role LIKE ?';
      $list[] = '%fox%';
      break;

    case 'lovers':
      $query .= 'role LIKE ?';
      $list[] = '% lovers%';
      break;

    case 'quiz':
      $query .= 'role LIKE ?';
      $list[] = '%quiz%';
      break;
    }

    DB::Prepare($query, $list);
    return DB::Count();
  }
}
