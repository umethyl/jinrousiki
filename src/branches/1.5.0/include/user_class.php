<?php
class User{
  public $uname;
  public $user_no;
  public $main_role;
  public $live;
  public $role_list    = array();
  public $partner_list = array();
  public $updated      = array();
  public $dead_flag    = false;
  public $suicide_flag = false;
  public $revive_flag  = false;
  public $lost_flag    = false;

  function __construct($role = NULL){
    if(is_null($role)) return;
    $this->role = $role;
    $this->ParseCompoundParameters();
  }

  function ParseCompoundParameters(){ $this->ParseRoles(); }

  //役職情報の展開処理
  function ParseRoles($role = NULL){
    //初期化処理
    if(isset($role)) $this->role = $role;
    $this->partner_list = array();

    //展開用の正規表現をセット
    $regex_partner = '/([^\[]+)\[([^\]]+)\]/'; //恋人型 (role[id])
    $regex_status  = '/([^-]+)-(.+)/';         //憑狼型 (role[date-id])

    //展開処理
    $role_list = array();
    foreach(explode(' ', $this->role) as $role){
      if(preg_match($regex_partner, $role, $match_partner)){
	$role_list[] = $match_partner[1];
	if(preg_match($regex_status, $match_partner[2], $match_status))
	  $this->partner_list[$match_partner[1]][$match_status[1]] = $match_status[2];
	else
	  $this->partner_list[$match_partner[1]][] = $match_partner[2];
      }
      else{
	$role_list[] = $role;
      }
    }

    //代入処理
    $this->role_list = array_unique($role_list);
    $this->main_role = $this->role_list[0];
  }

  //役職の再パース処理
  function ReparseRoles(){ $this->ParseRoles($this->GetRole()); }

  //指定したユーザーデータのセットを名前つき配列にして返します。
  //このメソッドは extract 関数を使用してオブジェクトのプロパティを
  //迅速にローカルに展開するために使用できます。 (現在は未使用)
  function ToArray($type = NULL){
    switch($type){
    case 'profiles':
      return array('profile'     => $this->profile,
		   'color'       => $this->color,
		   'icon_width'  => $this->icon_width,
		   'icon_height' => $this->icon_height);

    case 'flags':
      return array('dead_flag'    => $this->dead_flag,
		   'suicide_flag' => $this->suicide_flag,
		   'revive_flag'  => $this->revive_flag);

    case 'roles':
      return array('main_role'    => $this->main_role,
		   'role_list'    => $this->role_list,
		   'partner_list' => $this->partner_list);

    default:
      return array('user_no'     => $this->user_no,
		   'uname'       => $this->uname,
		   'handle_name' => $this->handle_name,
		   'role'        => $this->role,
		   'profile'     => $this->profile,
		   'icon'        => $this->icon_filename,
		   'color'       => $this->color);
    }
  }

  //ユーザ ID 取得
  function GetID($role = NULL){
    return isset($role) ? $role . '[' . $this->user_no . ']' : $this->user_no;
  }

  //HN 取得 (システムメッセージ用)
  function GetHandleName($uname, $result = NULL){
    global $USERS;

    $stack = array($this->handle_name, $USERS->GetHandleName($uname, true));
    if(isset($result)) $stack[] = $result;
    return implode("\t", $stack);
  }

  //役職取得
  function GetRole(){
    return array_key_exists('role', $this->updated) ? $this->updated['role'] : $this->role;
  }

  //メイン役職取得
  function GetMainRole($virtual = false){
    return $virtual && isset($this->virtual_role) ? $this->virtual_role : $this->main_role;
  }

  //所属陣営取得
  function GetCamp($win = false){
    global $USERS;

    $type = $win ? 'win_camp' : 'main_camp';
    if(! property_exists($this, $type)) $USERS->SetCamp($this, $type);
    return $this->$type;
  }

  //拡張情報取得
  function GetPartner($type, $fill = false){
    $stack = array_key_exists($type, $this->partner_list) ? $this->partner_list[$type] : NULL;
    return is_array($stack) ? $stack : ($fill ? array() : NULL);
  }

  //メイン役職の拡張情報取得
  function GetMainRoleTarget(){
    return array_shift($this->GetPartner($this->main_role, true));
  }

  //日数に応じた憑依先の ID 取得
  function GetPossessedTarget($type, $today){
    if(is_null($stack = $this->GetPartner($type))) return false;

    $date_list = array_keys($stack);
    krsort($date_list);
    foreach($date_list as $date){
      if($date <= $today) return $stack[$date];
    }
    return false;
  }

  //死の宣告系の宣告日取得
  function GetDoomDate($role){ return max($this->GetPartner($role)); }

  //仮想的な生死判定
  function IsDeadFlag($strict = false){
    if(! $strict) return NULL;
    if($this->suicide_flag) return true;
    if($this->revive_flag)  return false;
    if($this->dead_flag)    return true;
    return NULL;
  }

  //生存フラグ判定
  function IsLive($strict = false){
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == 'live' : ! $dead;
  }

  //死亡フラグ判定
  function IsDead($strict = false){
    $dead = $this->IsDeadFlag($strict);
    return is_null($dead) ? $this->live == 'dead' || $this->IsDrop() : $dead;
  }

  //蘇生辞退フラグ判定
  function IsDrop(){ return $this->live == 'drop'; }

  //同一ユーザ判定
  function IsSame($uname){ return $this->uname == $uname; }

  //同一 HN 判定
  function IsSameName($handle_name){ return $this->handle_name == $handle_name; }

  //自分と同一ユーザ判定
  function IsSelf(){
    global $SELF;
    return $this->IsSame($SELF->uname);
  }

  //身代わり君判定
  function IsDummyBoy($strict = false){
    global $ROOM;
    return $this->IsSame('dummy_boy') && ! ($strict && $ROOM->IsQuiz());
  }

  //役職判定
  function IsRole($role){
    $stack = func_get_args();
    $list  = $this->role_list;
    if($stack[0] === true){ //仮想役職対応
      array_shift($stack);
      if(isset($this->virtual_role)) $list[] = $this->virtual_role;
    }
    if(is_array($stack[0])) $stack = $stack[0];
    return count($stack) > 1 ? count(array_intersect($stack, $list)) > 0 :
      in_array($stack[0], $list);
  }

  //役職グループ判定
  function IsRoleGroup($role){
    $stack = func_get_args();
    $role_list = $this->role_list;
    if($stack[0] === true){
      array_shift($stack);
      if(isset($this->virtual_role)) $role_list[] = $this->virtual_role;
    }
    if(is_array($stack[0])) $stack = $stack[0];
    foreach($stack as $target){
      foreach($role_list as $role){
	if(strpos($role, $target) !== false) return true;
      }
    }
    return false;
  }

  //生存 + 役職判定
  function IsLiveRole($role, $strict = false){
    return $this->IsLive($strict) && $this->IsRole($role);
  }

  //生存 + 役職グループ判定
  function IsLiveRoleGroup($role){
    return $this->IsLive(true) && $this->IsRoleGroup(func_get_args());
  }

  //同一陣営判定
  function IsCamp($camp, $win = false){ return $this->GetCamp($win) == $camp; }

  //拡張判定
  function IsPartner($type, $target){
    if(is_null($partner_list = $this->GetPartner($type))) return false;
    if(is_array($target)){
      if(! array_key_exists($type, $target)) return false;
      if(! is_array($target_list = $target[$type])) return false;
      return count(array_intersect($partner_list, $target_list)) > 0;
    }
    else{
      return in_array($target, $partner_list);
    }
  }

  //能力喪失判定
  function IsActive($role = NULL){
    return (is_null($role) || $this->IsRole($role)) &&
      ! $this->lost_flag && ! $this->IsRole('lost_ability');
  }

  //孤立系役職判定
  function IsLonely(){ return $this->IsRole('mind_lonely') || $this->IsRoleGroup('silver'); }

  //男性判定
  function IsMale(){ return $this->sex == 'male'; }

  //女性判定
  function IsFemale(){ return $this->sex == 'female'; }

  //共有者系判定
  function IsCommon($talk = false){
    return $this->IsRoleGroup('common') && ! ($talk && $this->IsRole('dummy_common'));
  }

  //人狼系判定
  function IsWolf($talk = false){
    return $this->IsRoleGroup('wolf') && ! ($talk && $this->IsLonely());
  }

  //覚醒天狼判定
  function IsSiriusWolf($full = true){
    global $USERS;

    if(! $this->IsRole('sirius_wolf')) return false;
    $type = $full ? 'ability_full_sirius_wolf' : 'ability_sirius_wolf';
    if(! property_exists($this, $type)){
      $stack = $USERS->GetLivingWolves();
      $this->ability_sirius_wolf      = count($stack) < 3;
      $this->ability_full_sirius_wolf = count($stack) == 1;
    }
    return $this->$type;
  }

  //妖狐陣営判定
  function IsFox($talk = false){
    return $this->IsRoleGroup('fox') && ! ($talk && ($this->IsChildFox() || $this->IsLonely()));
  }

  //子狐系判定
  function IsChildFox($vote = false){
    $stack = array('child_fox', 'sex_fox', 'stargazer_fox', 'jammer_fox');
    if(! $vote) array_push($stack, 'monk_fox', 'miasma_fox', 'howl_fox', 'critical_fox');
    return $this->IsRole($stack);
  }

  //鬼陣営判定
  function IsOgre(){ return $this->IsRoleGroup('ogre', 'yaksa'); }

  //鵺系判定
  function IsUnknownMania(){
    return $this->IsRole('unknown_mania', 'wirepuller_mania', 'fire_mania', 'sacrifice_mania',
			 'resurrect_mania', 'revive_mania');
  }

  //恋人判定
  function IsLovers(){ return $this->IsRole('lovers'); }

  //難題耐性判定
  function IsChallengeLovers(){
    global $ROOM;
    return 1 < $ROOM->date && $ROOM->date < 5 && $this->IsRole('challenge_lovers');
  }

  //ジョーカー所持者判定
  function IsJoker($shift = false){
    global $ROOM, $USERS;

    if(! $this->IsRole('joker')) return false;
    if($ROOM->IsFinished()){
      if(! property_exists($this, 'joker_flag')) $USERS->SetJoker();
      return $this->joker_flag;
    }
    elseif($this->IsDead()) return false;

    $date = $ROOM->date - ($shift ? 1 : 0);
    if($date == 1 || $ROOM->IsNight()) $date++;
    return $this->GetDoomDate('joker') == $date;
  }

  //期間限定表示役職
  function IsDoomRole($role){
    global $ROOM;
    return $this->IsRole($role) && $this->GetDoomDate($role) == $ROOM->date;
  }

  //護衛成功済み判定
  function IsFirstGuardSuccess($uname){
    $flag = ! (property_exists($this, 'guard_success') && in_array($uname, $this->guard_success));
    $this->guard_success[] = $uname;
    return $flag;
  }

  //毒能力の発動判定
  function IsPoison(){
    global $ROOM;

    //旱魃、無毒・連毒者
    if($ROOM->IsEvent('no_poison') ||
       ! $this->IsRoleGroup('poison') || $this->IsRole('chain_poison')) return false;
    if($this->IsRole('poison_guard'))    return $ROOM->IsNight(); //騎士
    if($this->IsRole('incubate_poison')) return $ROOM->date >= 5; //潜毒者
    if($this->IsRole('dummy_poison'))    return $ROOM->IsDay();   //夢毒者
    return true;
  }

  //蘇生能力者判定
  function IsReviveGroup($active = false){
    return ($this->IsRoleGroup('cat') || $this->IsRole('revive_medium', 'revive_fox')) &&
      ! ($active && ! $this->IsActive());
  }

  //蘇生制限判定
  function IsReviveLimited(){
    return $this->IsRoleGroup('cat', 'revive') || $this->IsLovers() || $this->IsDrop() ||
      $this->IsRole('detective_common', 'scarlet_vampire', 'resurrect_mania') ||
      (property_exists($this, 'possessed_reset') && $this->possessed_reset);
  }

  //暗殺反射判定
  function IsRefrectAssassin(){
    global $ROOM;

    if($ROOM->IsEvent('no_reflect_assassin') || $this->IsDead(true)) return false; //無効判定

    //常時反射
    if($this->IsRole('reflect_guard', 'detective_common', 'cursed_fox', 'soul_vampire') ||
       $this->IsSiriusWolf(false) || $this->IsChallengeLovers()) return true;

    //確率反射
    if($this->IsRole('cursed_brownie')) $rate = 30;
    elseif($this->IsOgre()){
      //天候判定
      if($ROOM->IsEvent('full_ogre')) return true;
      if($ROOM->IsEvent('seal_ogre')) return false;

      if($this->IsRole('sacrifice_ogre'))
	$rate = 50;
      elseif($this->IsRole('west_ogre', 'east_ogre', 'north_ogre', 'south_ogre', 'incubus_ogre',
			   'wise_ogre', 'power_ogre', 'revive_ogre', 'power_yaksa', 'dowser_yaksa'))
	$rate = 40;
      elseif($this->IsRole('power_yaksa'))
	$rate = 30;
      elseif($this->IsRoleGroup('yaksa'))
	$rate = 20;
      else
	$rate = 30;
    }
    else return false;

    return $rate >= mt_rand(1, 100);
  }

  //憑依能力者判定 (被憑依者とコード上で区別するための関数)
  function IsPossessedGroup(){
    return $this->IsRole('possessed_wolf', 'possessed_mad', 'possessed_fox');
  }

  //憑依制限判定
  function IsPossessedLimited(){
    return $this->IsPossessedGroup() ||
      $this->IsRole(
        'detective_common', 'revive_priest', 'revive_pharmacist', 'revive_brownie', 'revive_doll',
	'revive_wolf', 'revive_mad', 'revive_cupid', 'scarlet_vampire', 'revive_ogre',
	'revive_avenger', 'resurrect_mania');
  }

  //呪返し判定
  function IsCursed(){
    global $ROOM;
    return ! $ROOM->IsEvent('no_cursed') && $this->IsLive(true) && $this->IsRoleGroup('cursed');
  }

  //嘘つき判定
  function IsLiar(){ return $this->DistinguishLiar() == 'psycho_mage_liar'; }

  //遺言制限判定
  function IsLastWordsLimited($save = false){
    $stack = array('reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words');
    if($save) $stack[] = 'possessed_exchange';
    return $this->IsRoleGroup('escaper') || $this->IsRole($stack);
  }

  //特殊耐性判定
  function IsAvoid($quiz = false){
    $stack = array('detective_common');
    if($quiz) $stack[] = 'quiz';
    return $this->IsRole($stack) || $this->IsSiriusWolf() || $this->IsChallengeLovers();
  }

  //投票済み判定
  function IsVoted($vote_data, $action, $not_action = NULL){
    return (isset($not_action) && array_key_exists($not_action, $vote_data) &&
	    is_array($vote_data[$not_action]) &&
	    array_key_exists($this->uname, $vote_data[$not_action])) ||
      ($action == 'WOLF_EAT' ? isset($vote_data[$action]) :
       isset($vote_data[$action][$this->uname]));
  }

  //所属陣営判別 (ラッパー)
  function DistinguishCamp(){
    global $ROLE_DATA;
    return $ROLE_DATA->DistinguishCamp($this->main_role);
  }

  //所属役職グループ陣営判別 (ラッパー)
  function DistinguishRoleGroup(){
    global $ROLE_DATA;
    return $ROLE_DATA->DistinguishRoleGroup($this->main_role);
  }

  //精神鑑定
  function DistinguishLiar(){
    return $this->IsOgre() ? 'ogre' :
      ((($this->IsRoleGroup('mad', 'dummy') || $this->IsRole('suspect', 'unconscious')) &&
	! $this->IsRole('swindle_mad')) ? 'psycho_mage_liar' : 'psycho_mage_normal');
  }

  //霊能鑑定
  function DistinguishNecromancer($reverse = false){
    if($this->IsOgre()) return 'ogre';
    if($this->IsRoleGroup('vampire') || $this->IsRole('cute_chiroptera')) return 'chiroptera';
    if($this->IsChildFox()) return 'child_fox';
    if($this->IsRole('white_fox', 'black_fox', 'mist_fox', 'phantom_fox', 'sacrifice_fox',
		     'possessed_fox', 'cursed_fox')){
      return 'fox';
    }
    if($this->IsRole('boss_wolf', 'mist_wolf', 'phantom_wolf', 'cursed_wolf', 'possessed_wolf')){
      return $this->main_role;
    }
    return ($this->IsWolf() xor $reverse) ? 'wolf' : 'human';
  }

  //未投票チェック
  function CheckVote($vote_data){
    global $ROOM;

    if($this->IsDummyBoy() || $this->IsDead()) return true;
    if($this->IsDoomRole('death_note')){
      if(! $this->IsVoted($vote_data, 'DEATH_NOTE_DO', 'DEATH_NOTE_NOT_DO')) return false;
    }
    if($this->IsWolf()) return $this->IsVoted($vote_data, 'WOLF_EAT');
    if($this->IsRoleGroup('mage')) return $this->IsVoted($vote_data, 'MAGE_DO');
    if($this->IsRole('voodoo_killer')) return $this->IsVoted($vote_data, 'VOODOO_KILLER_DO');
    if($this->IsRole('jammer_mad', 'jammer_fox')){
      return $this->IsVoted($vote_data, 'JAMMER_MAD_DO');
    }
    if($this->IsRole('voodoo_mad')) return $this->IsVoted($vote_data, 'VOODOO_MAD_DO');
    if($this->IsRole('emerald_fox')){
      return ! $this->IsActive() || $this->IsVoted($vote_data, 'MAGE_DO');
    }
    if($this->IsRole('voodoo_fox')) return $this->IsVoted($vote_data, 'VOODOO_FOX_DO');
    if($this->IsChildFox(true)) return $this->IsVoted($vote_data, 'CHILD_FOX_DO');
    if(($this->IsRoleGroup('fairy') && ! $this->IsRole('mirror_fairy', 'sweet_fairy')) ||
       $this->IsRole('enchant_mad')){
      return $this->IsVoted($vote_data, 'FAIRY_DO');
    }

    if($ROOM->date == 1){ //初日限定
      if($this->IsRole('mind_scanner', 'presage_scanner')){
	return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
      }
      if($this->IsRoleGroup('cupid', 'angel') ||
	 $this->IsRole('dummy_chiroptera', 'mirror_fairy', 'sweet_fairy')){
	return $this->IsVoted($vote_data, 'CUPID_DO');
      }
      if($this->IsRoleGroup('duelist', 'avenger', 'patron')){
	return $this->IsVoted($vote_data, 'DUELIST_DO');
      }
      if($this->IsRoleGroup('mania')) return $this->IsVoted($vote_data, 'MANIA_DO');

      if($ROOM->IsOpenCast()) return true;
      if($this->IsRole('evoke_scanner')) return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
      return true;
    }

    //二日目以降
    if($this->IsRoleGroup('guard')) return $this->IsVoted($vote_data, 'GUARD_DO');
    if($this->IsRole('reporter')) return $this->IsVoted($vote_data, 'REPORTER_DO');
    if($this->IsRole('anti_voodoo')) return $this->IsVoted($vote_data, 'ANTI_VOODOO_DO');
    if($this->IsRoleGroup('assassin') || $this->IsRole('doom_fox')){
      $event = $ROOM->IsEvent('force_assassin_do') ? NULL : 'ASSASSIN_NOT_DO';
      return $this->IsVoted($vote_data, 'ASSASSIN_DO', $event);
    }
    if($this->IsRole('clairvoyance_scanner')) return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
    if($this->IsRole('barrier_wizard')) return $this->IsVoted($vote_data, 'SPREAD_WIZARD_DO');
    if($this->IsRoleGroup('wizard') && ! $this->IsRole('spiritism_wizard', 'philosophy_wizard')){
      return $this->IsVoted($vote_data, 'WIZARD_DO');
    }
    if($this->IsRoleGroup('escaper')) return $this->IsVoted($vote_data, 'ESCAPE_DO');
    if($this->IsRole('dream_eater_mad')) return $this->IsVoted($vote_data, 'DREAM_EAT');
    if($this->IsRole('trap_mad', 'trap_fox')){
      return ! $this->IsActive() || $this->IsVoted($vote_data, 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
    }
    if($this->IsRole('snow_trap_mad')){
      return $this->IsVoted($vote_data, 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
    }
    if($this->IsRole('possessed_mad', 'possessed_fox')){
      return ! $this->IsActive() || $this->IsVoted($vote_data, 'POSSESSED_DO', 'POSSESSED_NOT_DO');
    }
    if($this->IsRoleGroup('vampire')) return $this->IsVoted($vote_data, 'VAMPIRE_DO');
    if($this->IsOgre()){
      $event = $ROOM->IsEvent('force_assassin_do') ? NULL : 'OGRE_NOT_DO';
      return $this->IsVoted($vote_data, 'OGRE_DO', $event);
    }
    if($ROOM->IsOpenCast()) return true;
    if($this->IsReviveGroup(true)){
      return $this->IsVoted($vote_data, 'POISON_CAT_DO', 'POISON_CAT_NOT_DO');
    }
    return true;
  }

  //役職情報から表示情報を作成する
  function GenerateRoleName($main_only = false){
    global $ROLE_DATA;

    $str = $ROLE_DATA->GenerateRoleTag($this->main_role); //メイン役職
    if($main_only) return $str;
    if(($role_count = count($this->role_list)) < 2) return $str; //サブ役職
    $count = 1;
    foreach($ROLE_DATA->sub_role_group_list as $class => $role_list){
      foreach($role_list as $sub_role){
	if(! $this->IsRole($sub_role)) continue;
	switch($sub_role){
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
	$str .= $ROLE_DATA->GenerateRoleTag($sub_role, $css, true);
	if(++$count >= $role_count) break 2;
      }
    }
    return $str;
  }

  //役職をパースして省略名を返す
  function GenerateShortRoleName($heaven = false, $main_only = false){
    global $ROLE_DATA, $USERS;

    if(empty($this->main_role)) return;
    if($main_only && isset($this->short_role_main)){ //キャッシュ判定
      return $this->short_role_main;
    }
    elseif(isset($this->short_role)){
      return $this->short_role;
    }

    //メイン役職を取得
    $camp = $this->GetCamp();
    $name = $ROLE_DATA->short_role_list[$this->main_role];
    $str = '<span class="add-role"> [';
    $str .= $camp == 'human' ? $name : '<span class="' . $camp . '">' . $name . '</span>';
    if($main_only){
      $this->short_role_main = $this->handle_name . $str . ']</span>';
      return $this->short_role_main;
    }

    //サブ役職を追加
    $sub_role_list = array_slice($this->role_list, 1);
    $stack = array_intersect(array_keys($ROLE_DATA->short_role_list), $sub_role_list);
    foreach ($stack as $role) {
      $name = $ROLE_DATA->short_role_list[$role];
      switch ($role) {
      case 'lovers':
      case 'possessed_exchange':
      case 'challenge_lovers':
	$str .= '<span class="lovers">' . $name . '</span>';
	break;

      case 'infected':
      case 'psycho_infected':
	$str .= '<span class="vampire">' . $name . '</span>';
	break;

      case 'rival':
      case 'enemy':
      case 'supported':
	$str .= '<span class="duelist">' . $name . '</span>';
	break;

      default:
	$str .=  $name;
	break;
      }
    }
    $uname = $heaven ? $this->uname : $USERS->TraceExchange($this->user_no)->uname;
    $str .= '] (' . $uname . ')</span>';
    $this->short_role = $str;
    return $this->short_role;
  }

  //投票画面用アイコンタグ生成
  function GenerateVoteTag($icon_path, $checkbox){
    global  $ICON_CONF;

    return <<<EOF
<td><label for="{$this->user_no}">
<img src="{$icon_path}" style="border-color: {$this->color};"{$ICON_CONF->tag}>
<font color="{$this->color}">◆</font>{$this->handle_name}<br>
{$checkbox}</label></td>

EOF;
  }

  //個別 DB 更新処理
  function Update($item, $value){
    global $ROOM;

    if($ROOM->test_mode){
      PrintData($value, 'Change [' . $item . '] (' . $this->uname . ')');
      return;
    }
    $query = "WHERE room_no = {$this->room_no} AND uname = '{$this->uname}' AND user_no > 0";
    return SendQuery("UPDATE user_entry SET {$item} = '{$value}' {$query}", true);
  }

  //総合 DB 更新処理 (この関数はまだ実用されていません)
  function Save(){
    if(empty($this->updated)) return false;
    foreach($this->updated as $item){
      $update_list[] = "$item = '{$this->item}'";
    }
    $update = implode(', ', $update_list);
    $query = "WHERE room_no = {$this->room_no} AND uname = '{$this->uname}' AND user_no > 0";
    SendQuery("UPDATE user_entry SET {$update} {$query}", true);
  }

  //基幹死亡処理
  function ToDead(){
    if($this->IsDead(true)) return false;
    $this->Update('live', 'dead');
    $this->dead_flag = true;
    return true;
  }

  //蘇生処理
  function Revive($virtual = false){
    global $ROOM;

    if($this->IsLive(true)) return false;
    $this->Update('live', 'live');
    $this->revive_flag = true;
    if(! $virtual) $ROOM->SystemMessage($this->handle_name, 'REVIVE_SUCCESS');
    return true;
  }

  //役職更新処理
  function ChangeRole($role){
    $this->Update('role', $role);
    $this->updated['role'] = $role; //キャッシュ本体の更新は行わない
  }

  //役職置換処理
  function ReplaceRole($target, $replace){
    $this->ChangeRole(str_replace($target, $replace, $this->GetRole()));
  }

  //役職追加処理
  function AddRole($role){
    $base_role = $this->GetRole();
    if(in_array($role, explode(' ', $base_role))) return false; //同じ役職は追加しない
    $this->ChangeRole($base_role . ' ' . $role);
  }

  //仮想役職追加処理 (キャッシュ限定)
  function AddVirtualRole($role){
    if(! in_array($role, $this->role_list)) $this->role_list[] = $role;
  }

  //メイン役職追加処理
  function AddMainRole($role){
    $this->ReplaceRole($this->main_role, $this->main_role . '[' . $role . ']');
  }

  //死の宣告処理
  function AddDoom($date, $role = 'death_warrant'){
    global $ROOM;
    $this->AddRole($role . '[' . ($ROOM->date + $date) . ']');
  }

  //ジョーカーの移動処理
  function AddJoker($shift = false){
    global $ROOM;

    if($shift){ //一時的に前日に巻戻す
      $ROOM->date--;
      $ROOM->day_night = 'night';
    }
    $this->AddDoom(1, 'joker');
    $ROOM->SystemMessage($this->handle_name, 'JOKER_MOVED_' . $ROOM->day_night);

    if($shift){ //日時を元に戻す
      $ROOM->date++;
      $ROOM->day_night = 'day';
    }
  }

  /*
    このメソッドは橋姫実装時のために予約されています。
     スペースが２つ続いている箇所は空の役職と認識されるおそれがあります。
     本来はParseRole側でpreg_split()などを使用するべきですが、役職が減る状況の方が少ないため、
     削除側で調節するものとします。(2009-07-05 enogu)
  */
  /*
  function RemoveRole($role){
    $this->role = str_replace('  ', ' ', str_replace($role, '', $this->role));
    $this->updated[] = 'role';
    $this->ParseRoles();
  }
  */

  function LostAbility(){
    $this->AddRole('lost_ability');
    $this->lost_flag = true;
  }

  function ReturnPossessed($type){
    global $ROOM;

    $date = $ROOM->date + 1;
    $this->AddRole("${type}[{$date}-{$this->user_no}]");
  }

  //遺言を取得して保存する
  function SaveLastWords($handle_name = NULL){
    global $ROOM;

    if(! $this->IsDummyBoy() && $this->IsLastWordsLimited(true)) return; //スキップ判定
    if(is_null($handle_name)) $handle_name = $this->handle_name;
    if($ROOM->test_mode){
      $ROOM->SystemMessage($handle_name . ' (' . $this->uname . ')', 'LAST_WORDS');
      return;
    }

    $query = "SELECT last_words FROM user_entry WHERE room_no = {$this->room_no} " .
      "AND uname = '{$this->uname}' AND user_no > 0";
    if(($last_words = FetchResult($query)) != ''){
      $ROOM->SystemMessage($handle_name . "\t" . $last_words, 'LAST_WORDS');
    }
  }

  //投票処理
  function Vote($action, $target = NULL, $vote_number = NULL){
    global $RQ_ARGS, $ROOM;

    if($ROOM->test_mode){
      if($ROOM->IsDay()){
	$stack = array('uname' => $this->uname, 'target_uname' => $target,
		       'vote_number' => $vote_number);
	$RQ_ARGS->TestItems->vote->day[$this->uname] = $stack;
	//PrintData($stack, 'Vote');
      }
      else{
	PrintData("{$action}: {$target}", 'Vote');
      }
      return true;
    }
    $items = 'room_no, date, uname, situation';
    $values = "{$ROOM->id}, $ROOM->date, '{$this->uname}', '{$action}'";
    if(isset($target)){
      $items .= ', target_uname';
      $values .= ", '{$target}'";
    }
    if(isset($vote_number)){
      $items .= ', vote_number, vote_times';
      $values .= ", '{$vote_number}', '{$RQ_ARGS->vote_times}'";
    }
    return InsertDatabase('vote', $items, $values);
  }
}

class UserDataSet{
  public $room_no;
  public $rows   = array();
  public $kicked = array();
  public $names  = array();

  function __construct($request){
    $this->room_no = $request->room_no;
    $this->LoadRoom($request);
  }

  //村情報のロード処理
  function LoadRoom($request){
    if($request->IsVirtualRoom()){ //仮想モード
      $user_list = $request->TestItems->test_users;
      if(is_int($user_list)) $user_list = $this->RetriveByUserCount($user_list);
    }
    elseif(property_exists($request, 'entry_user') && $request->entry_user){ //入村処理用
      $user_list = $this->RetriveByEntryUser($request->room_no);
    }
    else{
      $user_list = $this->RetriveByRoom($request->room_no);
    }
    $this->LoadUsers($user_list);
  }

  //特定の村のユーザ情報を取得する
  function RetriveByRoom($room_no){
    $query = "SELECT
	room_no,
	user_no,
	uname,
	handle_name,
	sex,
	profile,
	role,
	live,
	last_load_day_night,
	ip_address = '' AS is_system,
	icon_filename,
	color,
	icon_width,
	icon_height
      FROM user_entry LEFT JOIN user_icon ON user_entry.icon_no = user_icon.icon_no
      WHERE room_no = {$room_no}
      ORDER BY user_no";
    return FetchObject($query, 'User');
  }

  //指定した人数分のユーザ情報を全村からランダムに取得する
  function RetriveByUserCount($user_count){
    mysql_query('SET @new_user_no := 0');
    $query = "SELECT
	users.room_no,
	(@new_user_no := @new_user_no + 1) AS user_no,
	users.uname,
	users.handle_name,
	users.sex,
	users.profile,
	users.role,
	users.live,
	users.last_load_day_night,
	users.ip_address = '' AS is_system,
	icons.icon_filename,
	icons.color,
	icons.icon_width,
	icons.icon_height
      FROM (SELECT room_no, uname FROM user_entry WHERE room_no > 0 GROUP BY uname) finder
	LEFT JOIN user_entry users USING(room_no, uname)
	LEFT JOIN user_icon icons USING(icon_no)
      ORDER BY RAND()
      LIMIT {$user_count}";
    return FetchObject($query, 'User');
  }

  //入村処理用のユーザデータを取得する
  function RetriveByEntryUser($room_no){
    $query = "SELECT room_no, user_no, uname, handle_name, live, ip_address
      FROM user_entry WHERE room_no = {$room_no} ORDER BY user_no";
    return FetchObject($query, 'User');
  }

  //取得したユーザ情報を User クラスでパースして登録する
  function LoadUsers($user_list){
    global $ROOM;

    if(! is_array($user_list)) return false;

    //初期化処理
    $this->rows   = array();
    $this->kicked = array();
    $this->names  = array();
    $kicked_user_no = 0;

    foreach($user_list as $user){
      $user->ParseCompoundParameters();
      if($user->user_no >= 0 && $user->live != 'kick'){ //KICK 判定
	$this->rows[$user->user_no] = $user;
      }
      else{
	$this->kicked[$user->user_no = --$kicked_user_no] = $user;
      }
      $this->names[$user->uname] = $user->user_no;
    }
    if(! $ROOM->log_mode) $this->SetEvent();
    return count($this->names);
  }

  function ParseCompoundParameters(){
    foreach($this->rows as $user) $user->ParseCompoundParameters();
  }

  //ユーザ ID -> ユーザ名変換
  function NumberToUname($id){ return $this->rows[$id]->uname; }

  //ユーザ名 -> ユーザ ID 変換
  function UnameToNumber($uname){
    return array_key_exists($uname, $this->names) ? $this->names[$uname] : NULL;
  }

  //HN -> ユーザ名変換
  function HandleNameToUname($handle_name){
    foreach($this->rows as $user){
      if($user->IsSameName($handle_name)) return $user->uname;
    }
    return NULL;
  }

  //ユーザ情報取得 (ユーザ ID 経由)
  function ByID($id){
    if(is_null($id)) return new User();
    $stack = $this->{ $id > 0 ? 'rows' : 'kicked' };
    return array_key_exists($id, $stack) ? $stack[$id] : new User();
  }

  //ユーザ情報取得 (ユーザ名経由)
  function ByUname($uname){ return $this->ByID($this->UnameToNumber($uname)); }

  //ユーザ情報取得 (HN 経由)
  function ByHandleName($handle_name){
    return $this->ByUname($this->HandleNameToUname($handle_name));
  }

  //ユーザ情報取得 (クッキー経由)
  function BySession(){
    global $SESSION;
    return $this->TraceExchange($SESSION->GetUser());
  }

  //憑依情報追跡
  function TraceVirtual($user_no, $type){
    global $ROOM;

    $user = $this->ByID($user_no);
    if(! $ROOM->IsPlaying()) return $user;
    if($type == 'possessed'){
      if(! $user->IsRole($type)) return $user;
    }
    elseif(! $user->IsPossessedGroup()){
      return $user;
    }

    $id = $user->GetPossessedTarget($type, $ROOM->date);
    return $id === false ? $user : $this->ByID($id);
  }

  //交換憑依情報追跡
  function TraceExchange($id){
    global $ROOM;

    $user = $this->ByID($id);
    $role = 'possessed_exchange';
    if(! $user->IsRole($role) || ! $ROOM->IsPlaying() ||
       (! $ROOM->log_mode && $user->IsDead())) return $user;

    $stack = $user->GetPartner($role);
    return is_array($stack) && $ROOM->date > 2 ? $this->ByID(array_shift($stack)) : $user;
  }

  //ユーザ情報取得 (憑依先ユーザ ID 経由)
  function ByVirtual($id){ return $this->TraceVirtual($id, 'possessed_target'); }

  //ユーザ情報取得 (憑依元ユーザ ID 経由)
  function ByReal($id){ return $this->TraceVirtual($id, 'possessed'); }

  //ユーザ情報取得 (憑依先ユーザ名経由)
  function ByVirtualUname($uname){ return $this->ByVirtual($this->UnameToNumber($uname)); }

  //ユーザ情報取得 (憑依元ユーザ名経由)
  function ByRealUname($uname){ return $this->ByReal($this->UnameToNumber($uname)); }

  //HN 取得
  function GetHandleName($uname, $virtual = false){
    $user = $virtual ? $this->ByVirtualUname($uname) : $this->ByUname($uname);
    return property_exists($user, 'handle_name') ? $user->handle_name : '';
  }

  //役職情報取得
  function GetRole($uname){ return $this->ByUname($uname)->role; }

  //ユーザ数カウント
  function GetUserCount($all = false){ return count($all ? $this->names : $this->rows); }

  //所属陣営を判定してキャッシュする
  function SetCamp($user, $type){
    if($type == 'win_camp' && $user->IsLovers()){
      $user->$type = 'lovers';
      return;
    }

    $target = $user;
    $stack  = array();
    while($target->IsUnknownMania()){ //鵺系ならコピー先を辿る
      $id = $target->GetMainRoleTarget();
      if(is_null($id) || in_array($id, $stack)) break;
      $stack[] = $id;
      $target  = $this->ByID($id);
    }

    //覚醒者・夢語部ならコピー先を辿る
    if($target->IsRole('soul_mania', 'dummy_mania') &&
       ! is_null($id = $target->GetMainRoleTarget())){
      $target = $this->ByID($id);
      if($target->IsRoleGroup('mania')) $target = $user; //神話マニア系なら元に戻す
    }
    $user->$type = $target->DistinguishCamp();
  }

  //特殊イベント情報を設定する
  function SetEvent($force = false){
    global $ROLE_DATA, $RQ_ARGS, $ROOM, $ROLES;

    if($ROOM->id < 1 || ! is_array($event_rows = $ROOM->GetEvent($force))) return;
    $room_date = $ROOM->date; //現在の日付を確保
    $base_date = $ROOM->date; //判定用の日付
    if(($ROOM->watch_mode || $ROOM->single_view_mode) && ! $RQ_ARGS->reverse_log) $base_date--;

    foreach($event_rows as $event){
      switch($event['type']){
      case 'VOTE_KILLED':
	$ROOM->date = $base_date;
	if((! $ROOM->log_mode || $ROOM->single_log_mode) && $ROOM->IsDay()) $ROOM->date--;
	$user = $this->ByRealUname($this->HandleNameToUname($event['message']));
	//PrintData($user->handle_name, "VOTE_KILLED: {$room_date} ({$ROOM->date})");
	$ROLES->actor = $user;
	foreach($ROLES->Load('event_day') as $filter) $filter->SetEvent($this, 'day');
	foreach($user->GetPartner('bad_status', true) as $id => $date){ //悪戯
	  if($date != $ROOM->date) continue;
	  $ROLES->actor = $this->ByID($id);
	  foreach($ROLES->Load('bad_status_day') as $filter) $filter->SetBadStatus($user);
	}
	break;

      case 'WOLF_KILLED':
	$ROOM->date = $base_date - 1;
	$user = $this->ByRealUname($this->HandleNameToUname($event['message']));
	//PrintData($user->handle_name, "WOLF_KILLED: {$room_date} ({$ROOM->date})");
	if(! $user->IsDummyBoy()){ //座敷童子系
	  $ROLES->actor = $user;
	  foreach($ROLES->Load('event_night') as $filter) $filter->SetEvent($this, 'night');
	}
	foreach($user->GetPartner('bad_status', true) as $id => $date){ //悪戯
	  if($date != $base_date) continue;
	  $ROLES->actor = $this->ByID($id);
	  foreach($ROLES->Load('bad_status_night') as $filter) $filter->SetBadStatus($user);
	}
	break;

      case 'WEATHER':
	$ROOM->event->weather = (int)$event['message']; //天候データを格納
	$ROOM->event->{$ROLE_DATA->weather_list[$ROOM->event->weather]['event']} = true;
	break;
      }
    }
    $ROOM->date = $room_date; //日付を元に戻す
    if($ROOM->IsEvent('hyper_critical')){
      $ROOM->event->critical_voter = true;
      $ROOM->event->critical_luck  = true;
    }
    elseif($ROOM->IsEvent('aurora')){
      $ROOM->event->blinder   = true;
      $ROOM->event->mind_open = true;
    }
    //PrintData($ROOM->event);

    if($ROOM->IsDay()){ //昼限定
      foreach($ROLES->event_virtual_day_list as $role){
	if($ROOM->IsEvent($role)){
	  foreach($this->rows as $user) $user->AddVirtualRole($role);
	}
      }
    }

    if($ROOM->IsPlaying()){ //昼夜両方
      foreach($ROLES->event_virtual_list as $role){
	if($ROOM->IsEvent($role)){
	  foreach($this->rows as $user) $user->AddVirtualRole($role);
	}
      }
      $ROLES->LoadMain(new User('shadow_fairy'))->BadStatus($this, $base_date); //影妖精の処理
      foreach($ROLES->LoadFilter('change_face') as $filter) $filter->BadStatus($this); //狢の処理
    }
  }

  //ジョーカーの最終所持者判定
  function SetJoker(){
    $id = NULL;
    $max_date = 1;
    foreach($this->rows as $user){
      if(! $user->IsRole('joker')) continue;
      $date = $user->GetDoomDate('joker');
      if($date > $max_date || ($date == $max_date && $user->IsLive())){
	$id = $user->user_no;
	$max_date = $date;
      }
      $user->joker_flag = false;
    }
    $this->ByID($id)->joker_flag = true;
    return $id;
  }

  //役職の出現判定関数 (現在は不使用)
  function IsAppear($role){
    $role_list = func_get_args();
    foreach($this->rows as $user){
      if($user->IsRole($role_list)) return true;
    }
    return false;
  }

  //霊界の配役公開判定
  function IsOpenCast(){
    global $ROOM;

    $evoke_scanner = array();
    $mind_evoke    = array();
    foreach($this->rows as $user){
      if($user->IsDummyBoy()) continue;
      if($user->IsRole('mind_evoke')){
	$mind_evoke = array_merge($mind_evoke, $user->GetPartner('mind_evoke'));
      }
      if($user->IsReviveGroup(true) || $user->IsRole('revive_mania')){
	if($user->IsLive()) return false;
      }
      elseif($user->IsRole('revive_priest')){
	if($user->IsActive()) return false;
      }
      elseif($user->IsRole('evoke_scanner')){
	if($user->IsLive()){
	  if($ROOM->date == 1) return false;
	  $evoke_scanner[] = $user->user_no;
	}
      }
      elseif($user->IsRole('soul_mania', 'dummy_mania')){
	if($ROOM->date == 1 || ! is_null($user->GetMainRoleTarget())) return false;
      }
    }
    return count(array_intersect($evoke_scanner, $mind_evoke)) < 1;
  }

  //仮想的な生死を返す
  function IsVirtualLive($user_no, $strict = false){
    //憑依されている場合は憑依者の生死を返す
    $real_user = $this->ByReal($user_no);
    if($real_user->user_no != $user_no) return $real_user->IsLive($strict);

    //憑依先に移動している場合は常に死亡扱い
    if($this->ByVirtual($user_no)->user_no != $user_no) return false;

    //憑依が無ければ本人の生死を返す
    return $this->ByID($user_no)->IsLive($strict);
  }

  //生存者を取得する
  function GetLivingUsers($strict = false){
    $stack = array();
    foreach($this->rows as $user){
      if($user->IsLive($strict)) $stack[] = $user->uname;
    }
    return $stack;
  }

  //生存している人狼を取得する
  function GetLivingWolves(){
    $stack = array();
    foreach($this->rows as $user){
      if($user->IsLive() && $user->IsWolf()) $stack[] = $user->uname;
    }
    return $stack;
  }

  //死亡処理
  function Kill($user_no, $reason){
    global $ROOM;

    $user = $this->ByReal($user_no);
    if(! $user->ToDead()) return false;

    $virtual_user = $this->ByVirtual($user->user_no);
    $ROOM->SystemMessage($virtual_user->handle_name, $reason);

    switch($reason){
    case 'NOVOTED_day':
    case 'NOVOTED_night':
    case 'POSSESSED_TARGETED':
      return true;

    default: //遺言処理
      $user->SaveLastWords($virtual_user->handle_name);
      if($user != $virtual_user) $virtual_user->SaveLastWords();
      return true;
    }
  }

  //突然死処理
  function SuddenDeath($user_no, $reason){
    global $MESSAGE, $ROOM;

    if(! $this->Kill($user_no, $reason)) return false;
    $user = $this->ByReal($user_no);
    $user->suicide_flag = true;

    $str = strpos($reason, 'NOVOTED') !== false ? 'sudden_death' : 'vote_sudden_death';
    $ROOM->Talk($this->GetHandleName($user->uname, true) . ' ' . $MESSAGE->$str);
    return true;
  }

  //ジョーカーの再配布処理
  function ResetJoker($shift = false){
    global $ROOM;

    if(! $ROOM->IsOption('joker')) return false;
    $stack = array();
    foreach($this->rows as $user){
      if($user->IsDead(true)) continue;
      if($user->IsJoker()) return; //現在の所持者が生存していた場合はスキップ
      $stack[] = $user;
    }
    if(count($stack) > 0) GetRandom($stack)->AddJoker($shift);
  }

  //デスノートの再配布処理 (オプションチェック判定は不要？)
  function ResetDeathNote(){
    global $ROOM;

    $stack = array();
    foreach($this->rows as $user){
      if($user->IsLive(true)) $stack[] = $user;
    }
    if(count($stack) < 1) return;
    $user = GetRandom($stack);
    $user->AddDoom(0, 'death_note');
    $ROOM->SystemMessage($user->handle_name, 'DEATH_NOTE_MOVED', -1);
  }

  //仮想役職リストの保存 (ログ処理用)
  function SaveRoleList(){
    foreach($this->rows as $user) $user->save_role_list = $user->role_list;
  }

  //仮想役職リストの初期化 (ログ処理用)
  function ResetRoleList(){
    foreach($this->rows as $user) $user->role_list = $user->save_role_list;
  }

  //保存処理 (実用されていません)
  function Save(){
    foreach($this->rows as $user) $user->Save();
  }

  //現在のリクエスト情報に基づいて新しいユーザーをデータベースに登録します。
  //この関数は実用されていません
  function RegisterByRequest(){
    extract($_REQUEST, EXTR_PREFIX_ALL, 'unsafe');
    session_regenerate_id();
    UserDataSet::Register(
      mysql_real_escape_string($unsafe_uname),
      mysql_real_escape_string($unsafe_password),
      mysql_real_escape_string($unsafe_handle_name),
      mysql_real_escape_string($unsafe_sex),
      mysql_real_escape_string($unsafe_profile),
      intval($unsafe_icon_no),
      mysql_real_escape_string($unsafe_role),
      $_SERVER['REMOTE_ADDR'],
      session_id()
    );
  }

  //ユーザー情報を指定して新しいユーザーをデータベースに登録します。(ドラフト：この機能はテストされていません)
  function Register($uname, $password, $handle_name, $sex, $profile, $icon_no, $role,
		    $ip_address = '', $session_id = ''){
    $items = 'room_no, user_no, uname, password, handle_name, sex, profile, icon_no, role';
    $values = "$this->room_no, " .
      "(SELECT MAX(user_no) + 1 FROM user_entry WHERE room_no = {$this->room_no}), " .
      "'$uname', '$password', '$handle_name', '$sex', '$profile', $icon_no, '$role'";
    InsertDatabase('user_entry', $items, $value);
    $USERS->Load();
  }
}
