<?php
class User{
  var $main_role;
  var $role_list = array();
  var $partner_list = array();
  var $dead_flag = false;
  var $suicide_flag = false;
  var $revive_flag = false;

  function ParseCompoundParameters(){ $this->ParseRoles(); }

  //指定したユーザーデータのセットを名前つき配列にして返します。
  //このメソッドは extract 関数を使用してオブジェクトのプロパティを
  //迅速にローカルに展開するために使用できます。 (現在は未使用)
  function ToArray($type = NULL){
    switch($type){
      case 'profiles':
	$result['profile'] = $this->profile;
	$result['color'] = $this->color;
	$result['icon_width'] = $this->icon_width;
	$result['icon_height'] = $this->icon_height;
	break;

    case 'flags':
      $result['dead_flag'] = $this->dead_flag;
      $result['suicide_flag'] = $this->suicide_flag;
      $result['revive_flag'] = $this->revive_flag;
      break;

    case 'roles':
      $result['main_role'] = $this->main_role;
      $result['role_list'] = $this->role_list;
      $result['partner_list'] = $this->partner_list;
      break;

    default:
      return array('user_no'     => $this->user_no,
		   'uname'       => $this->uname,
		   'handle_name' => $this->handle_name,
		   'role'        => $this->role,
		   'profile'     => $this->profile,
		   'icon'        => $this->icon_filename,
		   'color'       => $this->color);
    }
    return $result;
  }

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

  //ユーザ番号を取得
  function GetID($role = NULL){
    $id = $this->user_no;
    return is_null($role) ? $id : $role . '[' . $id . ']';
  }

  //現在の役職を取得
  function GetRole(){ return $this->updated['role'] ? $this->updated['role'] : $this->role; }

  //現在の所属陣営を取得
  function GetCamp($win = false){
    global $USERS;

    $type = $win ? 'win_camp' : 'main_camp';
    if(is_null($this->$type)) $USERS->SetCamp($this, $type);
    return $this->$type;
  }

  //拡張情報を取得
  function GetPartner($type, $fill = false){
    $stack = $this->partner_list[$type];
    return is_array($stack) ? $stack : ($fill ? array() : NULL);
  }

  //日数に応じた憑依先の ID を取得
  function GetPossessedTarget($type, $today){
    if(is_null($stack = $this->GetPartner($type))) return false;

    $date_list = array_keys($stack);
    krsort($date_list);
    foreach($date_list as $date){
      if($date <= $today) return $stack[$date];
    }
    return false;
  }

  //死の宣告系の宣告日を取得
  function GetDoomDate($role){ return max($this->GetPartner($role)); }

  //現在の仮想的な生死情報
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
    if(is_array($stack[0])) $stack = $stack[0];
    return count($stack) > 1 ? count(array_intersect($stack, $this->role_list)) > 0 :
      in_array($stack[0], $this->role_list);
  }

  //役職グループ判定
  function IsRoleGroup($role){
    $stack = func_get_args();
    if(is_array($stack[0])) $stack = $stack[0];
    foreach($stack as $target){
      foreach($this->role_list as $role){
	if(strpos($role, $target) !== false) return true;
      }
    }
    return false;
  }

  //拡張判定
  function IsPartner($type, $target){
    if(is_null($partner_list = $this->GetPartner($type))) return false;
    if(is_array($target)){
      if(! is_array($target_list = $target[$type])) return false;
      return count(array_intersect($partner_list, $target_list)) > 0;
    }
    else{
      return in_array($target, $partner_list);
    }
  }

  //生存 + 役職判定
  function IsLiveRole($role, $strict = false){
    return $this->IsLive($strict) && $this->IsRole($role);
  }

  //生存 + 役職グループ判定
  function IsLiveRoleGroup($role){
    return $this->IsLive(true) && $this->IsRoleGroup(func_get_args());
  }

  //失効タイプの役職判定
  function IsActive($role = NULL){
    return (is_null($role) || $this->IsRole($role)) &&
      ! $this->lost_flag && ! $this->IsRole('lost_ability');
  }

  //孤立系役職判定
  function IsLonely($role = NULL){
    return (is_null($role) || $this->IsRole($role)) &&
      ($this->IsRole('mind_lonely') || $this->IsRoleGroup('silver'));
  }

  //共有者系判定
  function IsCommon($talk = false){
    return $this->IsRoleGroup('common') && ! ($talk && $this->IsRole('dummy_common'));
  }

  //上海人形系判定 (人形遣いは含まない)
  function IsDoll(){
    return $this->IsRoleGroup('doll') && ! $this->IsRole('doll_master');
  }

  //人狼系判定
  function IsWolf($talk = false){
    return $this->IsRoleGroup('wolf') && ! ($talk && $this->IsLonely());
  }

  //妖狐系判定
  function IsFox($talk = false){
    return $this->IsRoleGroup('fox') && ! ($talk && ($this->IsChildFox() || $this->IsLonely()));
  }

  //子狐系判定
  function IsChildFox($vote = false){
    $stack = array('child_fox', 'sex_fox', 'stargazer_fox', 'jammer_fox');
    if(! $vote) array_push($stack, 'miasma_fox', 'howl_fox');
    return $this->IsRole($stack);
  }

  //襲撃耐性妖狐判定
  function IsResistFox(){
    return $this->IsFox() && ! $this->IsRole('white_fox', 'poison_fox') && ! $this->IsChildFox();
  }

  //鬼陣営判定
  function IsOgre(){ return $this->IsRoleGroup('ogre', 'yaksa'); }

  //恋人判定
  function IsLovers(){ return $this->IsRole('lovers'); }

  //憑依能力者判定 (被憑依者とコード上で区別するための関数)
  function IsPossessedGroup(){
    return $this->IsRole('possessed_wolf', 'possessed_mad', 'possessed_fox');
  }

  //蘇生能力者判定
  function IsReviveGroup($active = false){
    return ($this->IsRoleGroup('cat') || $this->IsRole('revive_medium', 'revive_fox')) &&
      ! ($active && ! $this->IsActive());
  }

  //覚醒天狼判定
  function IsSiriusWolf($full = true){
    global $USERS;

    if(! $this->IsRole('sirius_wolf')) return false;
    $type = $full ? 'ability_full_sirius_wolf' : 'ability_sirius_wolf';
    if(is_null($this->$type)){
      $stack = $USERS->GetLivingWolves();
      $this->ability_sirius_wolf      = count($stack) < 3;
      $this->ability_full_sirius_wolf = count($stack) == 1;
    }
    return $this->$type;
  }

  //幻系能力判定
  function IsAbilityPhantom(){
    return $this->IsLiveRoleGroup('phantom') && $this->IsActive();
  }

  //難題判定
  function IsChallengeLovers(){
    global $ROOM;
    return $ROOM->date > 1 && $ROOM->date < 5 && $this->IsRole('challenge_lovers');
  }

  //特殊耐性判定
  function IsAvoid($quiz = false){
    $stack = array('detective_common');
    if($quiz) $stack[] = 'quiz';
    return $this->IsRole($stack) || $this->IsSiriusWolf() || $this->IsChallengeLovers();
  }

  //毒能力の発動判定
  function IsPoison(){
    global $ROOM;

    if(! $this->IsRoleGroup('poison') || $this->IsRole('chain_poison')) return false; //無毒・連毒者
    if($this->IsRole('poison_guard')) return $ROOM->IsNight(); //騎士
    if($this->IsRole('incubate_poison')) return $ROOM->date >= 5; //潜毒者は 5 日目以降
    if($this->IsRole('dummy_poison')) return $ROOM->IsDay(); //夢毒者
    return true;
  }

  //狩り判定
  function IsHuntTarget(){
    return ($this->IsRoleGroup('mad') && ! $this->IsRole('mad', 'fanatic_mad', 'whisper_mad')) ||
      ($this->IsRoleGroup('vampire') && ! $this->IsRole('vampire')) ||
      $this->IsRole('phantom_fox', 'voodoo_fox', 'revive_fox', 'possessed_fox', 'doom_fox',
		    'cursed_fox', 'poison_chiroptera', 'cursed_chiroptera', 'boss_chiroptera');
  }

  //護衛制限判定
  function IsGuardLimited(){
    return $this->IsRole('detective_common', 'reporter', 'clairvoyance_scanner', 'doll_master') ||
      ($this->IsRoleGroup('priest') && ! $this->IsRole('revive_priest', 'crisis_priest')) ||
      $this->IsRoleGroup('assassin');
  }

  //暗殺反射判定
  function IsRefrectAssassin(){
    $rate = mt_rand(1, 100);
    return $this->IsLive(true) &&
      ($this->IsRole('reflect_guard', 'detective_common', 'cursed_fox', 'soul_vampire') ||
       $this->IsSiriusWolf(false) || $this->IsChallengeLovers() ||
       ($this->IsRole('cursed_brownie') && $rate <= 30) ||
       ($this->IsRole('sacrifice_ogre') && $rate <= 50) ||
       ($this->IsRole('west_ogre', 'east_ogre', 'north_ogre', 'south_ogre', 'incubus_ogre',
		      'power_ogre', 'revive_ogre', 'dowser_yaksa') && $rate <= 40) ||
       ($this->IsRoleGroup('ogre')  && $rate <= 30) ||
       ($this->IsRoleGroup('yaksa') && $rate <= 20));
  }

  //憑依制限判定
  function IsPossessedLimited(){
    return $this->IsRole('detective_common', 'revive_priest', 'revive_pharmacist',
			 'revive_brownie', 'revive_doll', 'revive_ogre') ||
      $this->IsPossessedGroup();
  }

  //蘇生制限判定
  function IsReviveLimited(){
    return $this->IsRoleGroup('cat', 'revive') || $this->IsRole('detective_common') ||
      $this->IsLovers() || $this->IsDrop() || $this->possessed_reset;
  }

  //遺言制限判定
  function IsLastWordsLimited($save = false){
    $stack = array('reporter', 'soul_assassin', 'evoke_scanner', 'no_last_words');
    if($save) $stack[] = 'possessed_exchange';
    return $this->IsRoleGroup('escaper') || $this->IsRole($stack);
  }

  //ジョーカー所持者判定
  function IsJoker($date){
    global $ROOM, $USERS;

    if(! $this->IsRole('joker')) return false;
    if($ROOM->IsFinished()){
      if(is_null($this->joker_flag)) $USERS->SetJoker();
      return $this->joker_flag;
    }
    elseif($this->IsDead()) return false;

    if($date == 1 || $ROOM->IsNight()) $date++;
    return $this->GetDoomDate('joker') == $date;
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

  //占い師の判定
  function DistinguishMage($reverse = false){
    //吸血鬼陣営・大蝙蝠は「蝙蝠」
    if($this->IsRoleGroup('vampire') || $this->IsRole('boss_chiroptera')) return 'chiroptera';
    if($this->IsOgre()) return 'ogre'; //鬼陣営は「鬼」

    //白狼か完全覚醒天狼以外の人狼・黒狐・不審者は「人狼」
    $result = ($this->IsWolf() && ! $this->IsRole('boss_wolf') && ! $this->IsSiriusWolf()) ||
      $this->IsRole('black_fox', 'suspect');
    return ($result xor $reverse) ? 'wolf' : 'human';
  }

  //精神鑑定士の判定
  function DistinguishLiar(){
    return $this->IsOgre() ? 'ogre' :
      ($this->IsRoleGroup('mad', 'dummy') || $this->IsRole('suspect', 'unconscious') ?
       'psycho_mage_liar' : 'psycho_mage_normal');
  }

  //ひよこ鑑定士の判定
  function DistinguishSex(){
    return $this->IsOgre() ? 'ogre' :
      ($this->IsRoleGroup('chiroptera', 'fairy', 'gold') ? 'chiroptera' : 'sex_' . $this->sex);
  }

  //占星術師の判定
  function DistinguishVoteAbility(){
    global $ROOM;
    return array_key_exists($this->uname, $ROOM->vote) || $this->IsWolf() ?
      'stargazer_mage_ability' : 'stargazer_mage_nothing';
  }

  //薬師の毒鑑定
  function DistinguishPoison(){
    global $ROOM;

    //非毒能力者・夢毒者
    if(! $this->IsRoleGroup('poison') || $this->IsRole('dummy_poison')) return 'nothing';

    if($this->IsRole('strong_poison')) return 'strong'; //強毒者

    //潜毒者は 5 日目以降に強毒を持つ
    if($this->IsRole('incubate_poison')) return $ROOM->date >= 5 ? 'strong' : 'nothing';

    //騎士・誘毒者・連毒者・毒橋姫
    if($this->IsRole('poison_guard', 'guide_poison', 'chain_poison', 'poison_jealousy')){
      return 'limited';
    }
    return 'poison';
  }

  //投票済み判定
  function IsVoted($vote_data, $action, $not_action = NULL){
    return (isset($not_action) && is_array($vote_data[$not_action]) &&
	    array_key_exists($this->uname, $vote_data[$not_action])) ||
      ($action == 'WOLF_EAT' ? isset($vote_data[$action]) :
       isset($vote_data[$action][$this->uname]));
  }

  //未投票チェック
  function CheckVote($vote_data){
    global $ROOM;

    if($this->IsDummyBoy() || $this->IsDead()) return true;
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
    if(($this->IsRoleGroup('fairy') && ! $this->IsRole('mirror_fairy')) ||
       $this->IsRole('enchant_mad')){
      return $this->IsVoted($vote_data, 'FAIRY_DO');
    }

    if($ROOM->date == 1){ //初日限定
      if($this->IsRole('mind_scanner', 'presage_scanner')){
	return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
      }
      if($this->IsRoleGroup('cupid', 'angel') || $this->IsRole('dummy_chiroptera', 'mirror_fairy')){
	return $this->IsVoted($vote_data, 'CUPID_DO');
      }
      if($this->IsRoleGroup('mania')) return $this->IsVoted($vote_data, 'MANIA_DO');

      if($ROOM->IsOpenCast()) return true;
      if($this->IsRole('evoke_scanner')) return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
      return true;
    }

    //二日目以降
    if($this->IsRoleGroup('escaper')) return $this->IsVoted($vote_data, 'ESCAPE_DO');
    if($this->IsRoleGroup('guard')) return $this->IsVoted($vote_data, 'GUARD_DO');
    if($this->IsRole('reporter')) return $this->IsVoted($vote_data, 'REPORTER_DO');
    if($this->IsRole('anti_voodoo')) return $this->IsVoted($vote_data, 'ANTI_VOODOO_DO');
    if($this->IsRoleGroup('assassin') || $this->IsRole('doom_fox')){
      return $this->IsVoted($vote_data, 'ASSASSIN_DO', 'ASSASSIN_NOT_DO');
    }
    if($this->IsRole('clairvoyance_scanner')) return $this->IsVoted($vote_data, 'MIND_SCANNER_DO');
    if($this->IsRole('dream_eater_mad')) return $this->IsVoted($vote_data, 'DREAM_EAT');
    if($this->IsRole('trap_mad')){
      return ! $this->IsActive() || $this->IsVoted($vote_data, 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
    }
    if($this->IsRole('snow_trap_mad')){
      return $this->IsVoted($vote_data, 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
    }
    if($this->IsRole('possessed_mad', 'possessed_fox')){
      return ! $this->IsActive() || $this->IsVoted($vote_data, 'POSSESSED_DO', 'POSSESSED_NOT_DO');
    }
    if($this->IsRoleGroup('vampire')) return $this->IsVoted($vote_data, 'VAMPIRE_DO');
    if($this->IsOgre()) return $this->IsVoted($vote_data, 'OGRE_DO', 'OGRE_NOT_DO');

    if($ROOM->IsOpenCast()) return true;
    if($this->IsReviveGroup(true)){
      return $this->IsVoted($vote_data, 'POISON_CAT_DO', 'POISON_CAT_NOT_DO');
    }
    return true;
  }

  //役職情報から表示情報を作成する
  function GenerateRoleName($main_only = false){
    global $ROOM, $ROLE_DATA;

    $str = $ROLE_DATA->GenerateRoleTag($this->main_role); //メイン役職
    if($main_only) return $str;
    if(($role_count = count($this->role_list)) < 2) return $str; //サブ役職
    $count = 1;
    foreach($ROLE_DATA->sub_role_group_list as $class => $role_list){
      foreach($role_list as $sub_role){
	if(! $this->IsRole($sub_role)) continue;
	$joker = $sub_role == 'joker' && $this->IsJoker($ROOM->date);
	$str .= $ROLE_DATA->GenerateRoleTag($sub_role, $joker ? 'wolf' : $class, true);
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
  function AddJoker($decriment = false){
    global $ROOM;

    if($decriment){ //一時的に前日に巻戻す
      $ROOM->date--;
      $ROOM->day_night = 'night';
    }
    $this->AddDoom(1, 'joker');
    $ROOM->SystemMessage($this->handle_name, 'JOKER_MOVED_' . $ROOM->day_night);

    if($decriment){ //日時を元に戻す
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

  function ReturnPossessed($type, $date){
    $this->AddRole("${type}[{$date}-{$this->user_no}]");
    return true;
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
      }
      //PrintData($stack, 'Vote');
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
  var $room_no;
  var $rows   = array();
  var $kicked = array();
  var $names  = array();

  function UserDataSet($request){ $this->__construct($request); }
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
    elseif($request->entry_user){ //入村処理用
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
    if($ROOM->log_mode) $this->SetEvent();
    return count($this->names);
  }

  function ParseCompoundParameters(){
    foreach($this->rows as $user) $user->ParseCompoundParameters();
  }

  //ユーザ ID - ユーザ名変換
  function NumberToUname($user_no){ return $this->rows[$user_no]->uname; }

  //ユーザ名 - ユーザ ID 変換
  function UnameToNumber($uname){ return $this->names[$uname]; }

  //HN - ユーザ名変換
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
  function TraceExchange($user_no){
    global $ROOM;

    $user = $this->ByID($user_no);
    $type = 'possessed_exchange';
    if(! $user->IsRole($type) || ! $ROOM->IsPlaying() ||
       (! $ROOM->log_mode && $user->IsDead())) return $user;

    $stack = $user->GetPartner($type);
    return is_array($stack) && $ROOM->date > 2 ? $this->ByID(array_shift($stack)) : $user;
  }

  //ユーザ情報取得 (憑依先ユーザ ID 経由)
  function ByVirtual($user_no){ return $this->TraceVirtual($user_no, 'possessed_target'); }

  //ユーザ情報取得 (憑依元ユーザ ID 経由)
  function ByReal($user_no){ return $this->TraceVirtual($user_no, 'possessed'); }

  //ユーザ情報取得 (憑依先ユーザ名経由)
  function ByVirtualUname($uname){ return $this->ByVirtual($this->UnameToNumber($uname)); }

  //ユーザ情報取得 (憑依元ユーザ名経由)
  function ByRealUname($uname){ return $this->ByReal($this->UnameToNumber($uname)); }

  //HN 取得
  function GetHandleName($uname, $virtual = false){
    $user = $virtual ? $this->ByVirtualUname($uname) : $this->ByUname($uname);
    return $user->handle_name;
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
    while($target->IsRole('unknown_mania', 'sacrifice_mania')){ //鵺系ならコピー先を辿る
      $id = array_shift($target->GetPartner($target->main_role, true));
      if(is_null($id) || in_array($id, $stack)) break;
      $stack[] = $id;
      $target  = $this->ByID($id);
    }

    //覚醒者・夢語部ならコピー先を辿る
    if($target->IsRole('soul_mania', 'dummy_mania') &&
       is_array($stack = $target->GetPartner($target->main_role))){
      $target = $this->ByID(array_shift($stack));
      if($target->IsRoleGroup('mania')) $target = $user; //神話マニア系なら元に戻す
    }
    $user->$type = $target->DistinguishCamp();
  }

  //特殊イベント情報を設定する
  function SetEvent($force = false){
    global $ICON_CONF, $ROLE_DATA, $RQ_ARGS, $ROOM;

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
	if($user->IsRole('mirror_fairy')){ //鏡妖精
	  $stack = array(); //決選投票対象者の ID リスト
	  foreach($user->GetPartner('mirror_fairy', true) as $key => $value){ //生存確認
	    if($this->IsVirtualLive($key))   $stack[] = $key;
	    if($this->IsVirtualLive($value)) $stack[] = $value;
	  }
	  if(count($stack) > 1) $ROOM->event->vote_duel = $stack;
	}

	foreach($user->GetPartner('bad_status', true) as $id => $date){ //傘化け
	  $status_user = $this->ByID($id);
	  if($status_user->IsRole('amaze_mad') && $date == $ROOM->date){
	    $ROOM->event->blind_vote = true;
	    break;
	  }
	}
	break;

      case 'WOLF_KILLED':
	$ROOM->date = $base_date - 1;
	$user = $this->ByRealUname($this->HandleNameToUname($event['message']));
	//PrintData($user->handle_name, "WOLF_KILLED: {$room_date} ({$ROOM->date})");
	if(! $user->IsDummyBoy() && $user->IsRole('history_brownie')){ //白澤
	  $ROOM->event->skip_night = true;
	}

	$stack = array('sun_fairy'   => 'invisible', 'moon_fairy'  => 'earplug',
		       'grass_fairy' => 'grassy',    'light_fairy' => 'mind_open',
		       'dark_fairy'  => 'blinder');
	foreach($user->GetPartner('bad_status', true) as $id => $date){ //妖精系
	  if($date != $base_date) continue;
	  $status_user = $this->ByID($id);
	  foreach($stack as $role => $event){
	    if($status_user->IsRole($role)) $ROOM->event->$event = true;
	  }
	  if($status_user->IsRole('enchant_mad')) $ROOM->event->same_face[] = $user->user_no;
	}
	break;
      }
    }
    $ROOM->date = $room_date; //日付を元に戻す
    //PrintData($ROOM->event);

    if($ROOM->IsDay()){ //昼限定
      foreach(array('invisible', 'grassy', 'blinder', 'earplug') as $role){
	if($ROOM->IsEvent($role)){
	  foreach($this->rows as $user) $user->AddVirtualRole($role);
	}
      }
    }

    if($ROOM->IsPlaying()){ //昼夜両方
      if($ROOM->IsEvent('mind_open')){
	foreach($this->rows as $user) $user->AddVirtualRole('mind_open');
      }

      //影妖精の処理
      $stack = array();
      foreach($this->rows as $user){
	foreach($user->GetPartner('bad_status', true) as $id => $date){
	  if($date != $base_date) continue;
	  $status_user = $this->ByID($id);
	  if($status_user->IsRole('shadow_fairy')){
	    $stack[$status_user->user_no] = array('icon'  => $user->icon_filename,
						  'color' => $user->color);
	  }
	}
      }
      foreach($stack as $id => $list){
	$user = $this->ByID($id);
	$user->color         = $list['color'];
	$user->icon_filename = $list['icon'];
      }

      do{ //狢のアイコン入れ替え処理
	if(! is_array($ROOM->event->same_face)) break;
	$target = $this->ById(GetRandom($ROOM->event->same_face));
	if(is_null($target->uname)) break;
	foreach($this->rows as $user) $user->icon_filename = $target->icon_filename;
      }while(false);
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
      if($user->IsRole('mind_evoke')){
	$mind_evoke = array_merge($mind_evoke, $user->GetPartner('mind_evoke'));
      }
      if($user->IsDummyBoy()) continue;
      if($user->IsReviveGroup(true)){
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
	if($ROOM->date == 1) return false;
	if(is_array($user->GetPartner($user->main_role))) return false;
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

  //生存している狼を取得する
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

    $user = $this->ByReal($user_no);
    if(! $this->Kill($user_no, $reason)) return false;
    $user->suicide_flag = true;

    $sentence = strpos($reason, 'NOVOTED') !== false ? 'sudden_death' : 'vote_sudden_death';
    $ROOM->Talk($this->GetHandleName($user->uname, true) . ' ' . $MESSAGE->$sentence);
    return true;
  }

  //ジョーカーの再配置処理
  function ResetJoker($decriment = false){
    global $ROOM;

    if(! $ROOM->IsOption('joker')) return false;
    $stack = array();
    foreach($this->rows as $user){
      if($user->IsDead(true)) continue;
      if($user->IsJoker($ROOM->date)) return; //現在の所持者が生存していた場合はスキップ
      $stack[] = $user->user_no;
    }
    if(count($stack) > 0) $this->ByID(GetRandom($stack))->AddJoker($decriment);
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
  function Save(){ foreach($this->rows as $user) $user->Save(); }

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
