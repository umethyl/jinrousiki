<?php
//-- 個別ユーザクラス --//
class User {
  public $uname;
  public $user_no;
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
    $regex_status  = '/([^-]+)-(.+)/';         //憑狼型 (role[date-id])

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
  public function Reparse() { $this->Parse($this->GetRole()); }

  //player 入れ替え処理
  public function ChangePlayer($id) {
    if (! isset(DB::$USER->player) || ! isset($this->role_id) || $this->role_id == $id) {
      return false;
    }
    $this->role_id = $id;
    $this->Parse(DB::$USER->player->roles[$id]);
    return true;
  }

  //夜の投票取得
  public function LoadVote($type, $not_type = '') {
    $query = DB::$ROOM->GetQueryHeader('vote', 'type', 'target_no') .
      sprintf(" AND date = %d AND vote_count = %d AND ", DB::$ROOM->date, DB::$ROOM->vote_count);
    if ($type == 'WOLF_EAT') {
      $query .= sprintf("type = '%s'", $type);
    }
    elseif ($not_type != '') {
      $str = "user_no = %d AND type IN ('%s', '%s')";
      $query .= sprintf($str, $this->user_no, $type, $not_type);
    }
    else {
      $query .= sprintf("user_no = %d AND type = '%s'", $this->user_no, $type);
    }
    return DB::FetchAssoc($query, true);
  }

  //遺言取得
  public function LoadLastWords() {
    $format = 'SELECT last_words FROM user_entry WHERE room_no = %d AND user_no = %d';
    return DB::FetchResult(sprintf($format, $this->room_no, $this->user_no));
  }

  //ユーザ ID 取得
  public function GetID($role = null) {
    return isset($role) ? sprintf('%s[%d]', $role, $this->user_no) : $this->user_no;
  }

  //HN 取得 (システムメッセージ用)
  public function GetHandleName($uname, $result = null) {
    $stack = array($this->handle_name, DB::$USER->GetHandleName($uname, true));
    if (isset($result)) $stack[] = $result;
    return implode("\t", $stack);
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
  public function GetCamp($win = false) {
    $type = $win ? 'win_camp' : 'main_camp';
    if (! isset($this->$type)) DB::$USER->SetCamp($this, $type);
    return $this->$type;
  }

  //拡張情報取得
  public function GetPartner($type, $fill = false) {
    $stack = array_key_exists($type, $this->partner_list) ? $this->partner_list[$type] : null;
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
  public function GetDoomDate($role) { return max($this->GetPartner($role)); }

  //周辺 ID を取得
  public function GetAround() {
    $max   = count(DB::$USER->rows);
    $num   = $this->user_no;
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

  //生存フラグ判定
  public function IsLive($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == 'live' : ! $dead;
  }

  //死亡フラグ判定
  public function IsDead($strict = false) {
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == 'dead' || $this->IsDrop() : $dead;
  }

  //蘇生辞退フラグ判定
  public function IsDrop() { return $this->live == 'drop'; }

  //同一ユーザ判定
  public function IsSame($uname) { return $this->uname == $uname; }

  //同一 HN 判定
  public function IsSameName($handle_name) { return $this->handle_name == $handle_name; }

  //自分と同一ユーザ判定
  public function IsSelf() { return $this->IsSame(DB::$SELF->uname); }

  //身代わり君判定
  public function IsDummyBoy($strict = false) {
    return $this->IsSame('dummy_boy') && ! ($strict && DB::$ROOM->IsQuiz());
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
    return count($stack) > 1 ? count(array_intersect($stack, $list)) > 0 :
      in_array($stack[0], $list);
  }

  //役職グループ判定
  public function IsRoleGroup($role) {
    $stack = func_get_args();
    $role_list = $this->role_list;
    if ($stack[0] === true) {
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
  public function IsCamp($camp, $win = false) { return $this->GetCamp($win) == $camp; }

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
  public function IsMale() { return $this->sex == 'male'; }

  //女性判定
  public function IsFemale() { return $this->sex == 'female'; }

  //共有者系判定
  public function IsCommon($talk = false) {
    return $this->IsRoleGroup('common') && ! ($talk && $this->IsRole('dummy_common'));
  }

  //人狼系判定
  public function IsWolf($talk = false) {
    return $this->IsRoleGroup('wolf') && ! ($talk && $this->IsLonely());
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

  //妖狐陣営判定
  public function IsFox($talk = false) {
    return $this->IsRoleGroup('fox') && ! ($talk && ($this->IsChildFox() || $this->IsLonely()));
  }

  //子狐系判定
  public function IsChildFox($vote = false) {
    $stack = array('child_fox', 'sex_fox', 'stargazer_fox', 'jammer_fox');
    if (! $vote) {
      array_push($stack, 'monk_fox', 'miasma_fox', 'howl_fox', 'vindictive_fox', 'critical_fox');
    }
    return $this->IsRole($stack);
  }

  //鬼陣営判定
  public function IsOgre() { return $this->IsRoleGroup('ogre', 'yaksa'); }

  //鵺系判定
  public function IsUnknownMania() {
    return $this->IsRole('unknown_mania', 'wirepuller_mania', 'fire_mania', 'sacrifice_mania',
			 'resurrect_mania', 'revive_mania');
  }

  //恋人判定
  public function IsLovers() { return $this->IsRole('lovers'); }

  //難題耐性判定
  public function IsChallengeLovers() {
    return 1 < DB::$ROOM->date && DB::$ROOM->date < 5 && $this->IsRole('challenge_lovers');
  }

  //ジョーカー所持者判定
  public function IsJoker($shift = false) {
    if (! $this->IsRole('joker')) return false;
    if (DB::$ROOM->IsFinished()) {
      if (! isset($this->joker_flag)) DB::$USER->SetJoker();
      return $this->joker_flag;
    }
    elseif ($this->IsDead()) {
      return false;
    }

    $date = DB::$ROOM->date - ($shift ? 1 : 0);
    if ($date == 1 || DB::$ROOM->IsNight()) $date++;
    return $this->GetDoomDate('joker') == $date;
  }

  //期間限定表示役職
  public function IsDoomRole($role) {
    return $this->IsRole($role) && $this->GetDoomDate($role) == DB::$ROOM->date;
  }

  //護衛成功済み判定
  public function IsFirstGuardSuccess($uname) {
    $flag = ! (isset($this->guard_success) && in_array($uname, $this->guard_success));
    $this->guard_success[] = $uname;
    return $flag;
  }

  //毒能力の発動判定
  public function IsPoison() {
    if (DB::$ROOM->IsEvent('no_poison') || ! $this->IsRoleGroup('poison')) return false; //無効判定
    return RoleManager::GetClass($this->main_role)->IsPoison();
  }

  //蘇生能力者判定
  public function IsReviveGroup($active = false) {
    return ($this->IsRoleGroup('cat') || $this->IsRole('revive_medium', 'revive_fox')) &&
      ! ($active && ! $this->IsActive());
  }

  //蘇生制限判定
  public function IsReviveLimited() {
    return $this->IsRoleGroup('cat', 'revive') || $this->IsLovers() || $this->IsDrop() ||
      $this->IsRole('detective_common', 'scarlet_vampire', 'resurrect_mania') ||
      (isset($this->possessed_reset) && $this->possessed_reset);
  }

  //暗殺反射判定
  public function IsRefrectAssassin() {
    if (DB::$ROOM->IsEvent('no_reflect_assassin') || $this->IsDead(true)) return false; //無効判定

    //常時反射
    if ($this->IsRole('reflect_guard', 'detective_common', 'cursed_fox', 'soul_vampire') ||
       $this->IsSiriusWolf(false) || $this->IsChallengeLovers()) return true;

    //確率反射
    if ($this->IsRole('cursed_brownie')) {
      $rate = 30;
    }
    elseif ($this->IsOgre()) {
      //天候判定
      if (DB::$ROOM->IsEvent('full_ogre')) return true;
      if (DB::$ROOM->IsEvent('seal_ogre')) return false;
      $rate = RoleManager::GetClass($this->main_role)->reflect_rate;
    }
    else {
      return false;
    }

    return $rate >= mt_rand(1, 100);
  }

  //憑依能力者判定 (被憑依者とコード上で区別するための関数)
  public function IsPossessedGroup() {
    return $this->IsRole('possessed_wolf', 'possessed_mad', 'possessed_fox');
  }

  //憑依制限判定
  public function IsPossessedLimited() {
    return $this->IsPossessedGroup() ||
      $this->IsRole(
        'detective_common', 'revive_priest', 'revive_pharmacist', 'revive_brownie', 'revive_doll',
	'revive_wolf', 'revive_mad', 'revive_cupid', 'scarlet_vampire', 'revive_ogre',
	'revive_avenger', 'resurrect_mania');
  }

  //呪返し判定
  public function IsCursed() {
    return ! DB::$ROOM->IsEvent('no_cursed') && $this->IsLive(true) && $this->IsRoleGroup('cursed');
  }

  //嘘つき判定
  public function IsLiar() { return $this->DistinguishLiar() == 'psycho_mage_liar'; }

  //遺言制限判定
  public function IsLastWordsLimited($save = false) {
    $stack = array('reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words');
    if ($save) $stack[] = 'possessed_exchange';
    return $this->IsRoleGroup('escaper') || $this->IsRole($stack);
  }

  //特殊耐性判定
  public function IsAvoid($quiz = false) {
    $stack = array('detective_common');
    if ($quiz) $stack[] = 'quiz';
    return $this->IsRole($stack) || $this->IsSiriusWolf() || $this->IsChallengeLovers();
  }

  //毒回避判定
  public function IsAvoidPoison() {
    return $this->IsRole('poison_vampire') || $this->IsAvoid(true);
  }

  //所属陣営判別 (ラッパー)
  public function DistinguishCamp() { return RoleData::DistinguishCamp($this->main_role); }

  //所属役職グループ陣営判別 (ラッパー)
  public function DistinguishRoleGroup() {
    return RoleData::DistinguishRoleGroup($this->main_role);
  }

  //精神鑑定
  public function DistinguishLiar() {
    return $this->IsOgre() ? 'ogre' :
      ((($this->IsRoleGroup('mad', 'dummy') || $this->IsRole('suspect', 'unconscious')) &&
	! $this->IsRole('swindle_mad')) ? 'psycho_mage_liar' : 'psycho_mage_normal');
  }

  //霊能鑑定
  public function DistinguishNecromancer($reverse = false) {
    if ($this->IsOgre()) return 'ogre';
    if ($this->IsRoleGroup('vampire') || $this->IsRole('cute_chiroptera')) return 'chiroptera';
    if ($this->IsChildFox()) return 'child_fox';
    if ($this->IsRole('white_fox', 'black_fox', 'mist_fox', 'phantom_fox', 'sacrifice_fox',
		     'possessed_fox', 'cursed_fox')) {
      return 'fox';
    }
    if ($this->IsRole('boss_wolf', 'mist_wolf', 'phantom_wolf', 'cursed_wolf', 'possessed_wolf')) {
      return $this->main_role;
    }
    return ($this->IsWolf() xor $reverse) ? 'wolf' : 'human';
  }

  //未投票チェック
  public function CheckVote(array $list) {
    if ($this->IsDummyBoy() || $this->IsDead()) return true;
    if ($this->IsDoomRole('death_note')) {
      if (! ((isset($list['DEATH_NOTE_NOT_DO']) &&
	      array_key_exists($this->user_no, $list['DEATH_NOTE_NOT_DO'])) ||
	     isset($list['DEATH_NOTE_DO'][$this->user_no]))) return false;
    }
    return RoleManager::LoadMain($this)->IsFinishVote($list);
  }

  //役職情報から表示情報を作成する
  public function GenerateRoleName($main_only = false) {
    $str = RoleData::GenerateRoleTag($this->main_role); //メイン役職
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
	$str .= RoleData::GenerateRoleTag($sub_role, $css, true);
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
    $name = @RoleData::$short_role_list[$this->main_role];
    $str  = '<span class="add-role"> [';
    $str .= $camp == 'human' ? $name : sprintf('<span class="%s">%s</span>', $camp, $name);
    if ($main_only) {
      $str = $this->handle_name . $str . ']</span>';
      if (isset($this->role_id)) DB::$USER->short_role_main[$this->role_id] = $str;
      return $str;
    }

    //サブ役職を追加
    $sub_role_list = array_slice($this->role_list, 1);
    $stack = array_intersect(array_keys(RoleData::$short_role_list), $sub_role_list);
    foreach ($stack as $role) {
      $name = RoleData::$short_role_list[$role];
      switch ($role) {
      case 'lovers':
      case 'possessed_exchange':
      case 'challenge_lovers':
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
	$str .=  $name;
	break;
      }
    }
    $uname = $heaven ? $this->uname : DB::$USER->TraceExchange($this->user_no)->uname;
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
<td><label for="{$this->user_no}">
<img src="{$icon_path}" style="border-color: {$this->color};" {$tag}>
<font color="{$this->color}">◆</font>{$this->handle_name}<br>
{$checkbox}</label></td>

EOF;
  }

  //個別 DB 更新処理
  public function Update($item, $value) {
    if (DB::$ROOM->test_mode) {
      if (is_null($value)) $value = 'NULL (reset)';
      Text::p($value, sprintf('Change [%s] (%s)', $item, $this->uname));
      return true;
    }
    $value  = is_null($value) ? 'NULL' : "'{$value}'";
    $format = 'UPDATE user_entry SET %s = %s WHERE room_no = %d AND user_no = %d';
    return DB::FetchBool(sprintf($format, $item, $value, $this->room_no, $this->user_no));
  }

  //ID 更新処理 (KICK 後処理用)
  public function UpdateID($id) {
    if (DB::$ROOM->test_mode) {
      Text::p(sprintf('%d -> %d: %s', $this->user_no, $id, $this->uname), 'Change ID');
      return;
    }
    $format = "UPDATE user_entry SET user_no = %d WHERE room_no = %d AND uname = '%s'";
    return DB::FetchBool(sprintf($format, $id, $this->room_no, $this->uname));
  }

  //player 更新処理
  public function UpdatePlayer() {
    if (! isset($this->updated['role'])) return true;
    $role = $this->updated['role'];
    if (DB::$ROOM->test_mode) {
      Text::p($role, sprintf('Player (%s)', $this->uname));
      return true;
    }
    $items  = 'room_no, date, scene, user_no, role';
    $values = sprintf("%d, %d, '%s', %d, '%s'",
		      DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, $this->user_no, $role);
    if (! DB::Insert('player', $items, $values)) return false;
    return $this->Update('role_id', mysql_insert_id());
  }

  //基幹死亡処理
  public function ToDead() {
    if ($this->IsDead(true)) return false;
    $this->Update('live', 'dead');
    $this->dead_flag = true;
    return true;
  }

  //蘇生処理
  public function Revive($virtual = false) {
    if ($this->IsLive(true)) return false;
    $this->Update('live', 'live');
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

  //ジョーカーの移動処理
  public function AddJoker($shift = false) {
    if ($shift) DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    $this->AddDoom(1, 'joker');
    DB::$ROOM->ResultDead($this->handle_name, 'JOKER_MOVED');
    if ($shift) DB::$ROOM->ShiftScene(); //日時を元に戻す
  }

  //能力喪失処理
  public function LostAbility() {
    $this->AddRole('lost_ability');
    $this->lost_flag = true;
  }

  //憑依解除処理
  public function ReturnPossessed($type) {
    $this->AddRole(sprintf('%s[%d-%d]', $type, DB::$ROOM->date + 1, $this->user_no));
  }

  //遺言を取得して保存する
  public function SaveLastWords($handle_name = null) {
    if (! $this->IsDummyBoy() && $this->IsLastWordsLimited(true)) return true; //スキップ判定
    if (is_null($handle_name)) $handle_name = $this->handle_name;
    if (DB::$ROOM->test_mode) {
      Text::p(sprintf('%s (%s)', $handle_name, $this->uname), 'LastWords');
      return true;
    }

    if (is_null($message = $this->LoadLastWords())) return true;

    $items  = 'room_no, date, handle_name, message';
    $values = sprintf("%d, %d, '%s', '%s'", DB::$ROOM->id, DB::$ROOM->date, $handle_name, $message);
    return DB::Insert('result_lastwords', $items, $values);
  }

  //投票処理
  public function Vote($action, $target = null, $vote_number = null) {
    if (DB::$ROOM->test_mode) {
      if (DB::$ROOM->IsDay()) {
	$stack = array('user_no'   => $this->user_no, 'uname' => $this->uname,
		       'target_no' => $target, 'vote_number'  => $vote_number);
	RQ::GetTest()->vote->day[$this->uname] = $stack;
	//Text::p($stack, 'Vote');
      }
      else {
	Text::p(sprintf('%s: %s: %s', $action, $this->uname, $target), 'Vote');
      }
      return true;
    }
    $items = 'room_no, date, scene, type, uname, user_no, vote_count';
    $values = sprintf("%d, %d, '%s', '%s', '%s', %d, %d",
		      DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, $action,
		      $this->uname, $this->user_no, DB::$ROOM->vote_count);
    if (isset($target)) {
      $items  .= ', target_no';
      $values .= sprintf(", '%s'", $target);
    }
    if (isset($vote_number)) {
      $items  .= ', vote_number, revote_count';
      $values .= sprintf(', %d, %d', $vote_number, RQ::$get->revote_count);
    }
    return DB::Insert('vote', $items, $values);
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
class UserDataSet {
  public $room_no;
  public $rows = array();
  public $kick = array();
  public $name = array();
  public $role = array();

  function __construct(RequestBase $request, $lock = false) {
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
  public function ByUname($uname) { return $this->ByID($this->UnameToNumber($uname)); }

  //ユーザ情報取得 (HN 経由)
  public function ByHandleName($handle_name) {
    return $this->ByUname($this->HandleNameToUname($handle_name));
  }

  //ユーザ情報取得 (クッキー経由)
  public function BySession() { return $this->TraceExchange(Session::GetUser()); }

  //交換憑依情報追跡
  public function TraceExchange($id) {
    $user = $this->ByID($id);
    $role = 'possessed_exchange';
    if (! $user->IsRole($role) || ! DB::$ROOM->IsPlaying() ||
	(! DB::$ROOM->log_mode && $user->IsDead())) {
      return $user;
    }

    $stack = $user->GetPartner($role);
    return is_array($stack) && DB::$ROOM->date > 2 ? $this->ByID(array_shift($stack)) : $user;
  }

  //ユーザ情報取得 (憑依先ユーザ ID 経由)
  public function ByVirtual($id) { return $this->TraceVirtual($id, 'possessed_target'); }

  //ユーザ情報取得 (憑依元ユーザ ID 経由)
  public function ByReal($id) { return $this->TraceVirtual($id, 'possessed'); }

  //ユーザ情報取得 (憑依先ユーザ名経由)
  public function ByVirtualUname($uname) { return $this->ByVirtual($this->UnameToNumber($uname)); }

  //ユーザ情報取得 (憑依元ユーザ名経由)
  public function ByRealUname($uname) { return $this->ByReal($this->UnameToNumber($uname)); }

  //HN 取得
  public function GetHandleName($uname, $virtual = false) {
    $user = $virtual ? $this->ByVirtualUname($uname) : $this->ByUname($uname);
    return property_exists($user, 'handle_name') ? $user->handle_name : '';
  }

  //役職情報取得
  public function GetRole($uname) { return $this->ByUname($uname)->role; }

  //ユーザ数カウント
  public function GetUserCount($all = false) { return count($all ? $this->name : $this->rows); }

  //所属陣営を判定してキャッシュする
  public function SetCamp(User $user, $type) {
    if ($type == 'win_camp' && $user->IsLovers()) {
      $user->$type = 'lovers';
      return;
    }

    $target = $user;
    $stack  = array();
    while ($target->IsUnknownMania()) { //鵺系ならコピー先を辿る
      $id = $target->GetMainRoleTarget();
      if (is_null($id) || in_array($id, $stack)) break;
      $stack[] = $id;
      $target  = $this->ByID($id);
    }

    //覚醒者・夢語部ならコピー先を辿る
    if ($target->IsRole('soul_mania', 'dummy_mania') &&
	! is_null($id = $target->GetMainRoleTarget())) {
      $target = $this->ByID($id);
      if ($target->IsRoleGroup('mania')) $target = $user; //神話マニア系なら元に戻す
    }
    $user->$type = $target->DistinguishCamp();
  }

  //特殊イベント情報セット
  public function SetEvent($force = false) {
    if (DB::$ROOM->id < 1 || ! is_array($event_list = DB::$ROOM->GetEvent($force))) return;
    //Text::p($event_list, 'Event[row]');
    foreach ($event_list as $event) {
      switch ($event['type']) {
      case 'WEATHER':
	DB::$ROOM->event->weather = (int)$event['message']; //天候データを格納
	DB::$ROOM->event->{RoleData::$weather_list[DB::$ROOM->event->weather]['event']} = true;
	break;

      case 'EVENT':
	DB::$ROOM->event->$event['message'] = true;
	break;

      case 'VOTE_DUEL':
	RoleManager::LoadMain($this->ByID($event['message']))->SetEvent($this);
	break;

      case 'SAME_FACE':
	DB::$ROOM->event->same_face = $event['message'];
	break;

      case 'BLIND_VOTE':
	$date = DB::$ROOM->date - (DB::$ROOM->IsDay() ? 1 : 0);
	DB::$ROOM->event->blind_vote = $date == $event['message'];
	break;
      }
    }

    if (DB::$ROOM->IsEvent('hyper_critical')) {
      DB::$ROOM->event->critical_voter = true;
      DB::$ROOM->event->critical_luck  = true;
    }
    elseif (DB::$ROOM->IsEvent('aurora')) {
      DB::$ROOM->event->blinder   = true;
      DB::$ROOM->event->mind_open = true;
    }
    //Text::p(DB::$ROOM->event, 'Event');

    if (DB::$ROOM->IsDay()) { //昼限定
      foreach (RoleManager::$event_virtual_day_list as $role) {
	if (DB::$ROOM->IsEvent($role)) {
	  foreach ($this->rows as $user) $user->AddVirtualRole($role);
	}
      }
    }

    if (DB::$ROOM->IsPlaying()) { //昼夜両方
      foreach (RoleManager::$event_virtual_list as $role) {
	if (DB::$ROOM->IsEvent($role)) {
	  foreach ($this->rows as $user) $user->AddVirtualRole($role);
	}
      }

      if ($this->IsAppear($role = 'shadow_fairy')) { //影妖精の処理
	$date = DB::$ROOM->date; //判定用の日付
	if ((DB::$ROOM->watch_mode || DB::$ROOM->single_view_mode) && ! RQ::$get->reverse_log) {
	  $date--;
	}
	RoleManager::GetClass($role)->BadStatus($this, $date);
      }
      if ($this->IsAppear($role = 'enchant_mad')) { //狢の処理
	RoleManager::GetClass($role)->BadStatus($this);
      }
    }
  }

  //ジョーカーの最終所持者判定
  public function SetJoker() {
    $id = null;
    $max_date = 1;
    foreach ($this->rows as $user) {
      if (! $user->IsRole('joker')) continue;
      $date = $user->GetDoomDate('joker');
      if ($date > $max_date || ($date == $max_date && $user->IsLive())) {
	$id = $user->user_no;
	$max_date = $date;
      }
      $user->joker_flag = false;
    }
    $this->ByID($id)->joker_flag = true;
    return $id;
  }

  //役職の出現判定
  public function IsAppear($role) {
    $role_list = func_get_args();
    return count(array_intersect($role_list, array_keys($this->role))) > 0;
  }

  //霊界の配役公開判定
  public function IsOpenCast() {
    $evoke_scanner = array();
    $mind_evoke    = array();
    foreach ($this->rows as $user) {
      if ($user->IsDummyBoy()) continue;
      if ($user->IsRole('mind_evoke')) {
	$mind_evoke = array_merge($mind_evoke, $user->GetPartner('mind_evoke'));
      }
      if ($user->IsReviveGroup(true) || $user->IsRole('revive_mania')) {
	if ($user->IsLive()) return false;
      }
      elseif ($user->IsRole('revive_priest')) {
	if ($user->IsActive()) return false;
      }
      elseif ($user->IsRole('evoke_scanner')) {
	if ($user->IsLive()) {
	  if (DB::$ROOM->date == 1) return false;
	  $evoke_scanner[] = $user->user_no;
	}
      }
      elseif ($user->IsRole('soul_mania', 'dummy_mania')) {
	if (DB::$ROOM->date == 1 || ! is_null($user->GetMainRoleTarget())) return false;
      }
    }
    return count(array_intersect($evoke_scanner, $mind_evoke)) < 1;
  }

  //仮想的な生死を返す
  public function IsVirtualLive($user_no, $strict = false) {
    //憑依されている場合は憑依者の生死を返す
    $real_user = $this->ByReal($user_no);
    if ($real_user->user_no != $user_no) return $real_user->IsLive($strict);

    //憑依先に移動している場合は常に死亡扱い
    if ($this->ByVirtual($user_no)->user_no != $user_no) return false;

    //憑依が無ければ本人の生死を返す
    return $this->ByID($user_no)->IsLive($strict);
  }

  //生存者を取得する
  public function GetLivingUsers($strict = false) {
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsLive($strict)) $stack[$user->user_no] = $user->uname;
    }
    return $stack;
  }

  //生存している人狼を取得する
  public function GetLivingWolves() {
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsLive() && $user->IsWolf()) $stack[$user->user_no] = $user->uname;
    }
    return $stack;
  }

  //死亡処理
  public function Kill($user_no, $reason, $type = null) {
    $user = $this->ByReal($user_no);
    if (! $user->ToDead()) return false;

    $virtual_user = $this->ByVirtual($user->user_no);
    DB::$ROOM->ResultDead($virtual_user->handle_name, $reason, $type);

    switch ($reason) {
    case 'NOVOTED':
    case 'POSSESSED_TARGETED':
      return true;

    default: //遺言処理
      $user->SaveLastWords($virtual_user->handle_name);
      if (! $virtual_user->IsSame($user->uname)) $virtual_user->SaveLastWords();
      return true;
    }
  }

  //突然死処理
  public function SuddenDeath($user_no, $reason, $type = null) {
    if (! $this->Kill($user_no, $reason, $type)) return false;

    $user = $this->ByReal($user_no);
    $user->suicide_flag = true;

    $str = $reason == 'NOVOTED' ? 'sudden_death' : 'vote_sudden_death';
    DB::$ROOM->Talk($this->GetHandleName($user->uname, true) . ' ' . Message::$$str);
    return true;
  }

  //ジョーカーの再配布処理
  public function ResetJoker($shift = false) {
    if (! DB::$ROOM->IsOption('joker')) return false;
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsDead(true)) continue;
      if ($user->IsJoker()) return; //現在の所持者が生存していた場合はスキップ
      $stack[] = $user;
    }
    if (count($stack) > 0) Lottery::Get($stack)->AddJoker($shift);
  }

  //デスノートの再配布処理 (オプションチェック判定は不要？)
  public function ResetDeathNote() {
    $stack = array();
    foreach ($this->rows as $user) {
      if ($user->IsLive(true)) $stack[] = $user;
    }
    if (count($stack) < 1) return;
    $user = Lottery::Get($stack);
    $user->AddDoom(0, 'death_note');
    DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    DB::$ROOM->ResultDead($user->handle_name, 'DEATH_NOTE_MOVED');
    DB::$ROOM->ShiftScene();
  }

  //仮想役職リストの保存 (ログ処理用)
  public function SaveRoleList() {
    foreach ($this->rows as $user) $user->save_role_list = $user->role_list;
  }

  //仮想役職リストの初期化 (ログ処理用)
  public function ResetRoleList() {
    foreach ($this->rows as $user) $user->role_list = $user->save_role_list;
  }

  //player の復元 (ログ処理用)
  public function ResetPlayer() {
    if (! isset($this->player->users)) return;
    foreach ($this->player->users as $id => $stack) {
      $this->ByID($id)->ChangePlayer(max($stack));
    }
  }

  //村情報のロード処理
  private function Load(RequestBase $request, $lock = false) {
    if ($request->IsVirtualRoom()) { //仮想モード
      $user_list = $request->GetTest()->test_users;
      if (is_int($user_list)) $user_list = $this->LoadRandom($user_list);
    }
    elseif (isset($request->retrive_type)) { //特殊モード
      switch ($request->retrive_type) {
      case 'entry_user': //入村処理
	$user_list = $this->LoadEntryUser($request->room_no);
	break;

      case 'beforegame': //ゲーム開始前
	$user_list = $this->LoadBeforegame($request->room_no);
	break;

      case 'day': //昼 + 下界
	$user_list = $this->LoadDay($request->room_no);
	break;
      }
    }
    else {
      $user_list = $this->LoadRoom($request->room_no, $lock);
    }
    if (class_exists('RoleManager')) RoleManager::$get = new StdClass;
    $this->Parse($user_list);
  }

  //特定の村のユーザ情報取得
  private function LoadRoom($room_no, $lock = false) {
    $query = <<<EOF
SELECT room_no, user_no, uname, handle_name, profile, sex, role, role_id, objection, live,
  last_load_scene, icon_filename, color
FROM user_entry LEFT JOIN user_icon USING (icon_no)
WHERE room_no = {$room_no} ORDER BY user_no ASC
EOF;
    if ($lock) $query .= ' FOR UPDATE';
    return DB::FetchObject($query, 'User');
  }

  //入村処理用のユーザデータ取得
  private function LoadEntryUser($room_no) {
    $query = <<<EOF
SELECT room_no, user_no, uname, handle_name, live, ip_address FROM user_entry
WHERE room_no = {$room_no} ORDER BY user_no ASC FOR UPDATE
EOF;
    return DB::FetchObject($query, 'User');
  }

  //ゲーム開始前のユーザデータ取得
  private function LoadBeforegame($room_no) {
    if ($room_no != DB::$ROOM->id) return null;
    $vote_count = DB::$ROOM->vote_count;
    $room_no    = DB::$ROOM->id;
    $query = <<<EOF
SELECT u.room_no, u.user_no, u.uname, handle_name, profile, sex, role, role_id, objection, live,
  last_load_scene, icon_filename, color, v.type AS vote_type
FROM user_entry AS u LEFT JOIN user_icon USING (icon_no) LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.vote_count = {$vote_count} AND
  u.user_no = v.user_no AND v.type = 'GAMESTART'
WHERE u.room_no = {$room_no} ORDER BY user_no ASC
EOF;
    return DB::FetchObject($query, 'User');
  }

  //昼 + 下界用のユーザデータを取得する
  private function LoadDay($room_no) {
    if ($room_no != DB::$ROOM->id) return null;
    $date       = DB::$ROOM->date;
    $vote_count = DB::$ROOM->vote_count;
    $room_no    = DB::$ROOM->id;
    $query = <<<EOF
SELECT u.room_no, u.user_no, u.uname, handle_name, profile, sex, role, role_id, objection, live,
  last_load_scene, icon_filename, color, v.target_no AS target_no
FROM user_entry AS u LEFT JOIN user_icon USING (icon_no) LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.date = {$date} AND v.vote_count = {$vote_count} AND
  u.user_no = v.user_no AND v.type = 'VOTE_KILL'
WHERE u.room_no = {$room_no} ORDER BY user_no ASC
EOF;
    return DB::FetchObject($query, 'User');
  }

  //指定した人数分のユーザ情報を全村からランダムに取得する (テスト用)
  private function LoadRandom($count) {
    mysql_query('SET @new_user_no := 0');
    $query = <<<EOF
SELECT room_no, (@new_user_no := @new_user_no + 1) AS user_no, uname, handle_name, profile,
  sex, role, role_id, objection, live, last_load_scene, icon_filename, color
FROM (SELECT room_no, uname FROM user_entry WHERE room_no > 0 GROUP BY uname) AS finder
  LEFT JOIN user_entry USING (room_no, uname) LEFT JOIN user_icon USING (icon_no)
ORDER BY RAND() LIMIT {$count}
EOF;
    return DB::FetchObject($query, 'User');
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
      if ($user->user_no >= 0 && $user->live != 'kick') { //KICK 判定
	$this->rows[$user->user_no] = $user;
	foreach ($user->role_list as $role) {
	  if (! empty($role)) $this->role[$role][] = $user->user_no;
	}
      }
      else {
	$this->kick[$user->user_no = --$kick] = $user;
      }
      $this->name[$user->uname] = $user->user_no;
    }
    if (! DB::$ROOM->log_mode) $this->SetEvent();
    return count($this->name);
  }

  //HN -> ユーザ名変換
  private function HandleNameToUname($handle_name) {
    foreach ($this->rows as $user) {
      if ($user->IsSameName($handle_name)) return $user->uname;
    }
    return null;
  }

  //憑依情報追跡
  private function TraceVirtual($user_no, $type) {
    $user = $this->ByID($user_no);
    if (! DB::$ROOM->IsPlaying()) return $user;
    if ($type == 'possessed') {
      if (! $user->IsRole($type)) return $user;
    }
    elseif (! $user->IsPossessedGroup()) {
      return $user;
    }

    $id = $user->GetPossessedTarget($type, DB::$ROOM->date);
    return $id === false ? $user : $this->ByID($id);
  }
}
