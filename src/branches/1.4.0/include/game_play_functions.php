<?php
//能力の種類とその説明を出力
function OutputAbility(){
  global $MESSAGE, $ROLE_DATA, $ROLE_IMG, $ROOM, $USERS, $SELF;

  if(! $ROOM->IsPlaying()) return false; //ゲーム中のみ表示する

  if($SELF->IsDead()){ //死亡したら口寄せ以外は表示しない
    echo '<span class="ability ability-dead">' . $MESSAGE->ability_dead . '</span><br>';
    if($SELF->IsRole('mind_evoke')) $ROLE_IMG->Output('mind_evoke');
    return;
  }

  if($SELF->IsRole('human', 'saint', 'executor', 'suspect', 'unconscious')){ //村人系
    $ROLE_IMG->Output('human');
  }
  elseif($SELF->IsRole('elder')) $ROLE_IMG->Output($SELF->main_role); //長老
  elseif($SELF->IsRole('scripter')){ //執筆者
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 4) OutputAbilityResult('ability_scripter', NULL);
  }
  elseif($SELF->IsRoleGroup('mage')){ //占い師系
    $ROLE_IMG->Output($SELF->IsRole('dummy_mage') ? 'mage' : $SELF->main_role);
    if($ROOM->date > 1) OutputSelfAbilityResult('MAGE_RESULT'); //占い結果
    if($ROOM->IsNight()) OutputVoteMessage('mage-do', 'mage_do', 'MAGE_DO'); //夜の投票
  }
  elseif($SELF->IsRole('voodoo_killer')){ //陰陽師
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 1) OutputSelfAbilityResult('VOODOO_KILLER_SUCCESS'); //占い結果
    //夜の投票
    if($ROOM->IsNight()) OutputVoteMessage('mage-do', 'voodoo_killer_do', 'VOODOO_KILLER_DO');
  }
  elseif($SELF->IsRoleGroup('necromancer')){ //霊能者系
    $ROLE_IMG->Output($SELF->IsRole('dummy_necromancer') ? 'necromancer' : $SELF->main_role);
    if($ROOM->date > 2 && ! $SELF->IsRole('yama_necromancer')){ //霊能結果
      OutputSelfAbilityResult(strtoupper($SELF->main_role) . '_RESULT');
    }
  }
  elseif($SELF->IsRoleGroup('medium')){ //巫女
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 1) OutputSelfAbilityResult('MEDIUM_RESULT'); //神託結果
    if($SELF->IsRole('revive_medium') && ! $ROOM->IsOpenCast()){ //風祝
      if($ROOM->date > 2) OutputSelfAbilityResult('POISON_CAT_RESULT'); //蘇生結果
      if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
	OutputVoteMessage('revive-do', 'revive_do', 'POISON_CAT_DO', 'POISON_CAT_NOT_DO');
      }
    }
  }
  elseif($SELF->IsRoleGroup('priest')){ //司祭系
    switch($SELF->main_role){ //役職に応じた名称を表示
    case 'crisis_priest':
      $ROLE_IMG->Output('human');
      break;

    case 'dummy_priest':
    case 'priest_jealousy':
      $ROLE_IMG->Output('priest');
      break;

    default:
      $ROLE_IMG->Output($SELF->main_role);
      break;
    }

    switch($SELF->main_role){ //役職に応じた神託結果を表示
    case 'priest': //司祭
      if($ROOM->date > 3 && ($ROOM->date % 2) == 0) OutputSelfAbilityResult('PRIEST_RESULT');
      break;

    case 'bishop_priest': //司教
      if($ROOM->date > 2 && ($ROOM->date % 2) == 1) OutputSelfAbilityResult('BISHOP_PRIEST_RESULT');
      break;

    case 'high_priest': //大司祭
      if($ROOM->date > 4 && ($ROOM->date % 2) == 1) OutputSelfAbilityResult('BISHOP_PRIEST_RESULT');
      if($ROOM->date > 5 && ($ROOM->date % 2) == 0) OutputSelfAbilityResult('PRIEST_RESULT');
      break;

    case 'dowser_priest': //探知師
      if($ROOM->date > 3 && ($ROOM->date % 2) == 0) OutputSelfAbilityResult('DOWSER_PRIEST_RESULT');
      break;

    case 'border_priest': //境界師
      if($ROOM->date > 2) OutputSelfAbilityResult('BORDER_PRIEST_RESULT');
      break;

    case 'crisis_priest': //預言者
      if($ROOM->date > 1) OutputSelfAbilityResult('CRISIS_PRIEST_RESULT');
      break;

    case 'dummy_priest': //夢司祭
      if($ROOM->date > 3 && ($ROOM->date % 2) == 0) OutputSelfAbilityResult('DUMMY_PRIEST_RESULT');
      break;

    case 'priest_jealousy': //恋司祭
      if($ROOM->date > 3 && ($ROOM->date % 2) == 0) OutputSelfAbilityResult('PRIEST_JEALOUSY_RESULT');
      break;
    }
  }
  elseif($SELF->IsRoleGroup('guard')){ //狩人系
    $ROLE_IMG->Output($SELF->IsRole('dummy_guard') ? 'guard' : $SELF->main_role);
    if($ROOM->date > 2){
      OutputSelfAbilityResult('GUARD_SUCCESS'); //護衛結果
      OutputSelfAbilityResult('GUARD_HUNTED');  //狩り結果
    }
    //夜の投票
    if($ROOM->date > 1 && $ROOM->IsNight()) OutputVoteMessage('guard-do', 'guard_do', 'GUARD_DO');
  }
  elseif($SELF->IsRole('reporter')){ //ブン屋
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 2) OutputSelfAbilityResult('REPORTER_SUCCESS'); //尾行結果
    if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
      OutputVoteMessage('guard-do', 'reporter_do', 'REPORTER_DO');
    }
  }
  elseif($SELF->IsRole('anti_voodoo')){ //厄神
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 2) OutputSelfAbilityResult('ANTI_VOODOO_SUCCESS'); //護衛結果
    if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
      OutputVoteMessage('guard-do', 'anti_voodoo_do', 'ANTI_VOODOO_DO');
    }
  }
  elseif($SELF->IsCommon()){ //共有者系
    $ROLE_IMG->Output($SELF->IsRole('dummy_common') ? 'common' : $SELF->main_role);

    //仲間情報を取得
    $stack = array();
    foreach($USERS->rows as $user){
      if($user->IsSelf()) continue;
      if($SELF->IsRole('dummy_common')){
	if($user->IsDummyBoy()) $stack[] = $user->handle_name;
      }
      elseif($user->IsCommon(true)){
	$stack[] = $user->handle_name;
      }
    }
    OutputPartner($stack, 'common_partner'); //仲間を表示
    unset($stack);
  }
  elseif($SELF->IsRoleGroup('cat')){ //猫又系
    $ROLE_IMG->Output($SELF->IsRole('eclipse_cat') ? 'revive_cat' : $SELF->main_role);

    if(! $ROOM->IsOpenCast()){
      if($ROOM->date > 2) OutputSelfAbilityResult('POISON_CAT_RESULT'); //蘇生結果
      if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
	OutputVoteMessage('revive-do', 'revive_do', 'POISON_CAT_DO', 'POISON_CAT_NOT_DO');
      }
    }
  }
  elseif($SELF->IsRoleGroup('pharmacist')){ //薬師系
    $ROLE_IMG->Output($SELF->IsRole('eclipse_pharmacist') ? 'alchemy_pharmacist' :
		      $SELF->main_role);
    if($ROOM->date > 2) OutputSelfAbilityResult('PHARMACIST_RESULT'); //鑑定結果
  }
  elseif($SELF->IsRoleGroup('assassin')){ //暗殺者系
    $ROLE_IMG->Output($SELF->IsRole('eclipse_assassin') ? 'assassin' : $SELF->main_role);
    if($ROOM->date > 2 && $SELF->IsRole('soul_assassin')){ //辻斬り
      OutputSelfAbilityResult('ASSASSIN_RESULT'); //暗殺結果
    }
    if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
      OutputVoteMessage('assassin-do', 'assassin_do', 'ASSASSIN_DO', 'ASSASSIN_NOT_DO');
    }
  }
  elseif($SELF->IsRoleGroup('scanner')){ //さとり系
    $ROLE_IMG->Output($SELF->main_role);

    if($SELF->IsRole('mind_scanner', 'presage_scanner') ||
       ($SELF->IsRole('evoke_scanner') && ! $ROOM->IsOpenCast())){ //さとり・イタコ・件
      if($ROOM->date > 1){ //自分のサトラレ系対象を表示
	$stack = array();
	switch($SELF->main_role){
	case 'mind_scanner': //さとり (サトラレ)
	  $role = 'mind_read';
	  break;

	case 'evoke_scanner': //イタコ (口寄せ)
	  $role = 'mind_evoke';
	  break;

	case 'presage_scanner': //件 (受託者)
	  $role = 'mind_presage';
	  break;
	}
	foreach($USERS->rows as $user){
	  if($user->IsPartner($role, $SELF->user_no)) $stack[] = $user->handle_name;
	}
	OutputPartner($stack, 'mind_scanner_target');
	unset($stack);
      }
      if($ROOM->date == 1 && $ROOM->IsNight()){ //初日夜の投票
	OutputVoteMessage('mind-scanner-do', 'mind_scanner_do', 'MIND_SCANNER_DO');
      }
    }
    elseif($SELF->IsRole('clairvoyance_scanner')){
      if($ROOM->date > 2) OutputSelfAbilityResult('CLAIRVOYANCE_RESULT'); //透視結果
      if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
	OutputVoteMessage('mind-scanner-do', 'mind_scanner_do', 'MIND_SCANNER_DO');
      }
    }
  }
  elseif($SELF->IsRoleGroup('brownie')){ //座敷童子系
    $ROLE_IMG->Output($SELF->main_role);
  }
  elseif($SELF->IsRoleGroup('doll')){ //上海人形系
    $ROLE_IMG->Output($SELF->main_role);
    if(! $SELF->IsRole('doll_master')){ //仲間表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsSelf()) continue;
	if($user->IsRole('doll_master') || $user->IsRoleGroup('scarlet')){
	  $stack['master'][] = $user->handle_name;
	}
	if($user->IsDoll()) $stack['doll'][] = $user->handle_name;
      }
      if(! $SELF->IsRole('silver_doll')){ //人形遣い (露西亜人形には見えない)
	OutputPartner($stack['master'], 'doll_master_list');
      }
      if($SELF->IsRole('friend_doll')) OutputPartner($stack['doll'], 'doll_partner'); //仏蘭西人形
      unset($stack);
    }
  }
  elseif($SELF->IsRoleGroup('escaper')){ //逃亡者系
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
      OutputVoteMessage('escape-do', 'escape_do', 'ESCAPE_DO');
    }
  }
  elseif($SELF->IsWolf()){ //人狼系
    $ROLE_IMG->Output($SELF->main_role);

    //仲間情報を収集
    $stack = array();
    foreach($USERS->rows as $user){
      if($user->IsSelf()) continue;
      if($user->IsWolf(true)){
	$stack['wolf'][] = $USERS->GetHandleName($user->uname, true);
      }
      elseif($user->IsRole('whisper_mad')){
	$stack['mad'][] = $user->handle_name;
      }
      elseif($user->IsRole('unconscious') || $user->IsRoleGroup('scarlet')){
	$stack['unconscious'][] = $user->handle_name;
      }
    }
    if($SELF->IsWolf(true)){
      OutputPartner($stack['wolf'], 'wolf_partner'); //仲間を表示
      OutputPartner($stack['mad'], 'mad_partner'); //囁き狂人を表示
    }
    if($ROOM->IsNight()){ //夜だけ無意識と紅狐を表示
      OutputPartner($stack['unconscious'], 'unconscious_list');
    }
    unset($stack);

    switch($SELF->main_role){ //特殊狼の処理
    case 'tongue_wolf': //舌禍狼
      if($ROOM->date > 1) OutputSelfAbilityResult('TONGUE_WOLF_RESULT'); //噛み結果
      break;

    case 'sex_wolf': //雛狼
      if($ROOM->date > 1) OutputSelfAbilityResult('SEX_WOLF_RESULT'); //性別情報
      break;

    case 'possessed_wolf': //憑狼
      if($ROOM->date > 1) OutputPossessedTarget(); //現在の憑依先を表示
      break;

    case 'sirius_wolf': //天狼
      switch(strval(count($USERS->GetLivingWolves()))){
      case '2':
	OutputAbilityResult('ability_sirius_wolf', NULL);
	break;

      case '1':
	OutputAbilityResult('ability_full_sirius_wolf', NULL);
	break;
      }
      break;
    }

    if($ROOM->IsNight()) OutputVoteMessage('wolf-eat', 'wolf_eat', 'WOLF_EAT'); //夜の投票
  }
  elseif($SELF->IsRoleGroup('mad')){ //狂人系
    $ROLE_IMG->Output($SELF->main_role);

    switch($SELF->main_role){
    case 'fanatic_mad': //狂信者
      //狼を表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsWolf(true)) $stack[] = $USERS->GetHandleName($user->uname, true);
      }
      OutputPartner($stack, 'wolf_partner');
      unset($stack);
      break;

    case 'whisper_mad': //囁き狂人
      //狼と囁き狂人を表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsSelf() || $user->IsRole('silver_wolf')) continue;
	if($user->IsWolf()){
	  $stack['wolf'][] = $USERS->GetHandleName($user->uname, true);
	}
	elseif($user->IsRole('whisper_mad')){
	  $stack['mad'][] = $user->handle_name;
	}
      }
      OutputPartner($stack['wolf'], 'wolf_partner');
      OutputPartner($stack['mad'], 'mad_partner');
      unset($stack);
      break;

    case 'jammer_mad': //月兎
      if($ROOM->IsNight()) OutputVoteMessage('wolf-eat', 'jammer_do', 'JAMMER_MAD_DO');
      break;

    case 'voodoo_mad': //呪術師
      if($ROOM->IsNight()) OutputVoteMessage('wolf-eat', 'voodoo_do', 'VOODOO_MAD_DO');
      break;

    case 'enchant_mad': //狢
      if($ROOM->IsNight()) OutputVoteMessage('fairy-do', 'fairy_do', 'FAIRY_DO');
      break;

    case 'dream_eater_mad': //獏
      if($ROOM->date > 1 && $ROOM->IsNight()){
	OutputVoteMessage('wolf-eat', 'dream_eat', 'DREAM_EAT');
      }
      break;

    case 'possessed_mad': //犬神
      if($ROOM->date > 2) OutputPossessedTarget(); //現在の憑依先を表示
      if($SELF->IsActive() && $ROOM->date > 1 && $ROOM->IsNight()){
	OutputVoteMessage('wolf-eat', 'possessed_do', 'POSSESSED_DO', 'POSSESSED_NOT_DO');
      }
      break;

    case 'trap_mad': //罠師
      if($SELF->IsActive() && $ROOM->date > 1 && $ROOM->IsNight()){
	OutputVoteMessage('wolf-eat', 'trap_do', 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
      }
      break;

    case 'snow_trap_mad': //雪女
      if($ROOM->date > 1 && $ROOM->IsNight()){
	OutputVoteMessage('wolf-eat', 'trap_do', 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO');
      }
      break;
    }
  }
  elseif($SELF->IsFox()){ //妖狐系
    $ROLE_IMG->Output($SELF->main_role);

    if(! $SELF->IsLonely()){ //仲間表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsSelf()) continue;
	if($user->IsFox(true)){
	  $stack['fox'][] = $user->handle_name;
	}
	elseif($user->IsChildFox() || $user->IsRoleGroup('scarlet')){
	  $stack['child_fox'][] = $user->handle_name;
	}
      }
      OutputPartner($stack['fox'], 'fox_partner'); //妖狐系
      OutputPartner($stack['child_fox'], 'child_fox_partner'); //子狐系
      unset($stack);
    }

    if($SELF->IsRole('jammer_fox')){ //月狐
      if($ROOM->IsNight()) OutputVoteMessage('wolf-eat', 'jammer_do', 'JAMMER_MAD_DO');
    }
    elseif($SELF->IsChildFox(true)){ //子狐系
      if($ROOM->date > 1) OutputSelfAbilityResult('CHILD_FOX_RESULT'); //占い結果
      if($ROOM->IsNight()) OutputVoteMessage('mage-do', 'mage_do', 'CHILD_FOX_DO'); //夜の投票
    }
    else{
      switch($SELF->main_role){
      case 'emerald_fox': //翠狐
	if($SELF->IsActive() && $ROOM->IsNight()){
	  OutputVoteMessage('mage-do', 'mage_do', 'MAGE_DO');
	}
	break;

      case 'voodoo_fox': //九尾
	if($ROOM->IsNight()) OutputVoteMessage('wolf-eat', 'voodoo_do', 'VOODOO_FOX_DO');
	break;

      case 'revive_fox': //仙狐
	if($ROOM->date > 2) OutputSelfAbilityResult('POISON_CAT_RESULT'); //蘇生結果
	//夜の投票
	if($SELF->IsActive() && $ROOM->date > 1 && $ROOM->IsNight() && ! $ROOM->IsOpenCast()){
	  OutputVoteMessage('revive-do', 'revive_do', 'POISON_CAT_DO', 'POISON_CAT_NOT_DO');
	}
	break;

      case 'possessed_fox': //憑狐
	if($ROOM->date > 2) OutputPossessedTarget(); //現在の憑依先を表示
	if($SELF->IsActive() && $ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
	  OutputVoteMessage('wolf-eat', 'possessed_do', 'POSSESSED_DO', 'POSSESSED_NOT_DO');
	}
	break;

      case 'doom_fox': //冥狐
	if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
	  OutputVoteMessage('assassin-do', 'assassin_do', 'ASSASSIN_DO', 'ASSASSIN_NOT_DO');
	}
	break;
      }
    }

    if($ROOM->date > 1 && ! ($SELF->IsRole('white_fox', 'poison_fox') || $SELF->IsChildFox())){
      OutputSelfAbilityResult('FOX_EAT'); //襲撃メッセージを表示
    }
  }
  elseif($SELF->IsRoleGroup('chiroptera')){ //蝙蝠系
    if($SELF->IsRole('dummy_chiroptera')){ //夢求愛者
      $ROLE_IMG->Output('self_cupid');

      //自分が矢を打った(つもり)の恋人 (自分自身含む) を表示
      $stack = $SELF->GetPartner('dummy_chiroptera');
      if(is_array($stack)){
	$stack[] = $SELF->user_no;
	asort($stack);
	$stack_pair = array();
	foreach($stack as $id) $stack_pair[] = $USERS->ById($id)->handle_name;
	OutputPartner($stack_pair, 'cupid_pair');
	unset($stack, $stack_pair);
      }

      if($ROOM->date == 1 && $ROOM->IsNight()){
	OutputVoteMessage('cupid-do', 'cupid_do', 'CUPID_DO'); //初日夜の投票
      }
    }
    else{
      $ROLE_IMG->Output($SELF->main_role);
    }
  }
  elseif($SELF->IsRoleGroup('fairy')){ //妖精系
    $ROLE_IMG->Output($SELF->main_role);
    if($SELF->IsRole('mirror_fairy')){ //鏡妖精
      if($ROOM->date == 1 && $ROOM->IsNight()){
	OutputVoteMessage('fairy-do', 'fairy_do', 'CUPID_DO'); //初日夜の投票
      }
    }
    else{
      if($ROOM->IsNight()) OutputVoteMessage('fairy-do', 'fairy_do', 'FAIRY_DO'); //夜の投票
    }
  }
  elseif($SELF->IsOgre()){ //鬼陣営
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 1 && $ROOM->IsNight()){ //夜の投票
      OutputVoteMessage('ogre-do', 'ogre_do', 'OGRE_DO', 'OGRE_NOT_DO');
    }
    if($ROOM->date > 2 && $SELF->IsRole('sacrifice_ogre')){ //酒呑童子
      //洗脳者を表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsRole('psycho_infected')) $stack[] = $user->handle_name;
      }
      OutputPartner($stack, 'psycho_infected_list');
      unset($stack);
    }
  }
  elseif($SELF->IsRole('incubate_poison')){ //潜毒者
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->date > 4) OutputAbilityResult('ability_poison', NULL);
  }
  elseif($SELF->IsRole('guide_poison')){ //誘毒者
    $ROLE_IMG->Output($SELF->main_role);
  }
  elseif($SELF->IsRole('chain_poison')){ //連毒者
    $ROLE_IMG->Output('human');
  }
  elseif($SELF->IsRoleGroup('poison')) $ROLE_IMG->Output('poison'); //埋毒者系
  elseif($SELF->IsRoleGroup('cupid', 'angel')){ //キューピッド系
    $ROLE_IMG->Output($SELF->main_role);

    //自分が矢を打った恋人 (自分自身含む) を表示
    $stack = array();
    foreach($USERS->rows as $user){
      if($user->IsPartner('lovers', $SELF->user_no)) $stack[] = $user->handle_name;
    }
    OutputPartner($stack, 'cupid_pair');
    unset($stack);

    if($SELF->IsRole('ark_angel') && $ROOM->date == 2){
      OutputSelfAbilityResult('SYMPATHY_RESULT'); //大天使は共感者情報を全て見ることが出来る
    }
    //初日夜の投票
    if($ROOM->date == 1 && $ROOM->IsNight()) OutputVoteMessage('cupid-do', 'cupid_do', 'CUPID_DO');
  }
  elseif($SELF->IsRoleGroup('jealousy')) $ROLE_IMG->Output($SELF->main_role); //橋姫
  elseif($SELF->IsRole('quiz')){ //出題者
    $ROLE_IMG->Output($SELF->main_role);
    if($ROOM->IsOptionGroup('chaos')) $ROLE_IMG->Output('quiz_chaos');
  }
  elseif($SELF->IsRoleGroup('vampire')){ //吸血鬼系
    $ROLE_IMG->Output($SELF->main_role);

    if($ROOM->date > 2){ //自分の感染者と洗脳者を表示
      $stack = array();
      foreach($USERS->rows as $user){
	if($user->IsPartner('infected', $SELF->user_no))
	  $stack['infected'][] = $user->handle_name;
	elseif($user->IsRole('psycho_infected'))
	  $stack['psycho_infected'][] = $user->handle_name;
      }
      OutputPartner($stack['infected'], 'infected_list');
      OutputPartner($stack['psycho_infected'], 'psycho_infected_list');
      unset($stack);
    }
    if($SELF->IsRole('soul_vampire')) OutputSelfAbilityResult('VAMPIRE_RESULT'); //吸血姫の吸血結果
    if($ROOM->date > 1 && $ROOM->IsNight()){
      OutputVoteMessage('vampire-do', 'vampire_do', 'VAMPIRE_DO'); //夜の投票
    }
  }
  elseif($SELF->IsRoleGroup('mania')){ //神話マニア
    $ROLE_IMG->Output($SELF->IsRole('dummy_mania') ? 'soul_mania' : $SELF->main_role);
    //初日夜の投票
    if($ROOM->date == 1 && $ROOM->IsNight()) OutputVoteMessage('mania-do', 'mania_do', 'MANIA_DO');
    if($ROOM->date == 2 && $SELF->IsRole('soul_mania', 'dummy_mania')){
      OutputSelfAbilityResult('MANIA_RESULT'); //覚醒者・夢語部のコピー結果
    }
  }

  //-- ここから兼任役職 --//
  $fix_display_list = array(); //常時表示する役職リスト

  //元神話マニアのコピー結果を表示
  if($SELF->IsRoleGroup('copied') && ($ROOM->date == 2 || $ROOM->date == 4)){
    OutputSelfAbilityResult('MANIA_RESULT');
  }
  array_push($fix_display_list, 'copied', 'copied_trick', 'copied_soul', 'copied_teller');

  $role = 'lost_ability'; //能力喪失 (舌禍狼・罠師ほか)
  if($SELF->IsRole($role)) $ROLE_IMG->Output($role);
  $fix_display_list[] = $role;

  if($SELF->IsLovers() || $SELF->IsRole('dummy_chiroptera')){ //恋人
    foreach($USERS->rows as $user){
      if(! $user->IsSelf() &&
	 ($user->IsPartner('lovers', $SELF->partner_list) ||
	  $SELF->IsPartner('dummy_chiroptera', $user->user_no))){
	$lovers_partner[] = $USERS->GetHandleName($user->uname, true);
      }
    }
    OutputPartner($lovers_partner, 'partner_header', 'lovers_footer');
  }
  $fix_display_list[] = 'lovers';

  $role = 'challenge_lovers'; //難題 (表示は2日目以降)
  if($SELF->IsRole($role) && $ROOM->date > 1) $ROLE_IMG->Output($role);
  $fix_display_list[] = $role;

  $role = 'possessed_exchange'; //交換憑依
  if($SELF->IsRole($role)){
    do{ //現在の憑依先を表示
      if(! is_array($stack = $SELF->GetPartner($role))) break;
      if(is_null($target = $USERS->ByID(array_shift($stack))->handle_name)) break;
      $ROOM->date < 3 ?
	OutputAbilityResult('exchange_header', $target, 'exchange_footer') :
	OutputAbilityResult('partner_header', $SELF->handle_name, 'possessed_target');
    }while(false);
  }
  $fix_display_list[] = $role;

  $role = 'joker'; //ジョーカー
  if($SELF->IsJoker($ROOM->date)) $ROLE_IMG->Output($role);
  $fix_display_list[] = $role;

  //ここからは憑依先の役職を表示
  $virtual_self = $USERS->ByVirtual($SELF->user_no);

  $role = 'febris'; //熱病
  if($virtual_self->IsRole($role) &&
     ($date = $virtual_self->GetDoomDate($role)) == $ROOM->date){
    OutputAbilityResult('febris_header', $date, 'sudden_death_footer');
  }
  $fix_display_list[] = $role;

  $role = 'frostbite'; //凍傷
  if($virtual_self->IsRole($role) &&
     ($date = $virtual_self->GetDoomDate($role)) == $ROOM->date){
    OutputAbilityResult('frostbite_header', $date, 'frostbite_footer');
  }
  $fix_display_list[] = $role;

  $role = 'death_warrant'; //死の宣告
  if($virtual_self->IsRole($role) &&
     ($date = $virtual_self->GetDoomDate($role)) >= $ROOM->date){
    OutputAbilityResult('death_warrant_header', $date, 'sudden_death_footer');
  }
  $fix_display_list[] = $role;

  $role = 'mind_open'; //公開者
  if($virtual_self->IsRole($role)) $ROLE_IMG->Output($role);
  $fix_display_list[] = $role;

  if($ROOM->date > 1){ //サトラレ系の表示は 2 日目以降
    foreach(array('mind_read', 'mind_evoke', 'mind_lonely') as $role){ //サトラレ・口寄せ・はぐれ者
      if($virtual_self->IsRole($role)) $ROLE_IMG->Output($role);
    }

    $role = 'mind_receiver'; //受信者
    if($virtual_self->IsRole($role)){
      $ROLE_IMG->Output($role);

      $stack = array();
      foreach($virtual_self->GetPartner($role, true) as $id){
	$stack[$id] = $USERS->ById($id)->handle_name;
      }
      ksort($stack);
      OutputPartner($stack, 'mind_scanner_target');
      unset($stack);
    }

    $role = 'mind_friend'; //共鳴者
    if($virtual_self->IsRole($role)){
      $ROLE_IMG->Output($role);

      $stack = array();
      foreach($USERS->rows as $user){
	if(! $user->IsSame($virtual_self->uname) &&
	   $user->IsPartner($role, $virtual_self->partner_list)){
	  $stack[$user->user_no] = $user->handle_name;
	}
      }
      ksort($stack);
      OutputPartner($stack, 'mind_friend_list');
      unset($stack);
    }

    $role = 'mind_sympathy'; //共感者
    if($virtual_self->IsRole($role)){
      $ROLE_IMG->Output($role);
      if($ROOM->date == 2) OutputSelfAbilityResult('SYMPATHY_RESULT');
    }

    $role = 'mind_presage'; //受託者
    if($virtual_self->IsRole($role) && $ROOM->date > 2) OutputSelfAbilityResult('PRESAGE_RESULT');
  }
  array_push($fix_display_list, 'mind_read', 'mind_evoke', 'mind_presage', 'mind_lonely',
	     'mind_receiver', 'mind_friend', 'mind_sympathy', 'infected', 'psycho_infected',
	     'possessed_target', 'possessed', 'bad_status', 'protected', 'changed_therian');

  //これ以降はサブ役職非公開オプションの影響を受ける
  if($ROOM->IsOption('secret_sub_role')) return;

  $role_keys_list    = array_keys($ROLE_DATA->sub_role_list);
  $hide_display_list = array('decide', 'plague', 'good_luck', 'bad_luck', 'critical_voter',
			     'critical_luck');
  $not_display_list  = array_merge($fix_display_list, $hide_display_list);
  $display_list      = array_diff($role_keys_list, $not_display_list);
  $target_list       = array_intersect($display_list, array_slice($virtual_self->role_list, 1));

  foreach($target_list as $role) $ROLE_IMG->Output($role);
}

//仲間を表示する
function OutputPartner($list, $header, $footer = NULL){
  global $ROLE_IMG;

  if(count($list) < 1) return false; //仲間がいなければ表示しない
  $list[] = '</td>';
  $str = '<table class="ability-partner"><tr>'."\n" .
    $ROLE_IMG->Generate($header, NULL, true) ."\n" .
    '<td>　' . implode('さん　', $list) ."\n";
  if($footer) $str .= $ROLE_IMG->Generate($footer, NULL, true) ."\n";
  echo $str . '</tr></table>'."\n";
}

//現在の憑依先を表示する
function OutputPossessedTarget(){
  global $USERS, $SELF;

  $type = 'possessed_target';
  if(is_null($stack = $SELF->GetPartner($type))) return;

  $target = $USERS->ByID($stack[max(array_keys($stack))])->handle_name;
  if($target != '') OutputAbilityResult('partner_header', $target, $type);
}

//個々の能力発動結果を表示する
/*
  一部の処理は、HN にタブが入るとパースに失敗する
  入村時に HN からタブを除く事で対応できるが、
  そもそもこのようなパースをしないといけない DB 構造に
  問題があるので、ここでは特に対応しない
*/
function OutputSelfAbilityResult($action){
  global $RQ_ARGS, $ROOM, $SELF;

  $header = NULL;
  $footer = 'result_';
  switch($action){
  case 'MAGE_RESULT':
    $type = 'mage';
    $header = 'mage_result';
    break;

  case 'VOODOO_KILLER_SUCCESS':
    $type = 'guard';
    $footer = 'voodoo_killer_success';
    break;

  case 'NECROMANCER_RESULT':
  case 'SOUL_NECROMANCER_RESULT':
  case 'ATTEMPT_NECROMANCER_RESULT':
  case 'DUMMY_NECROMANCER_RESULT':
    $type = 'necromancer';
    break;

  case 'MEDIUM_RESULT':
    $type = 'necromancer';
    $header = 'medium';
    break;

  case 'PRIEST_RESULT':
  case 'DUMMY_PRIEST_RESULT':
  case 'PRIEST_JEALOUSY_RESULT':
    $type = 'priest';
    $header = 'priest_header';
    $footer = 'priest_footer';
    break;

  case 'BISHOP_PRIEST_RESULT':
    $type = 'priest';
    $header = 'bishop_priest_header';
    $footer = 'priest_footer';
    break;

  case 'DOWSER_PRIEST_RESULT':
    $type = 'priest';
    $header = 'dowser_priest_header';
    $footer = 'dowser_priest_footer';
    break;

  case 'BORDER_PRIEST_RESULT':
    $type = 'mage';
    $header = 'border_priest_header';
    $footer = 'priest_footer';
    break;

  case 'CRISIS_PRIEST_RESULT':
    $type = 'crisis_priest';
    $header = 'side_';
    $footer = 'crisis_priest_result';
    break;

  case 'GUARD_SUCCESS':
    $type = 'guard';
    $footer = 'guard_success';
    break;

  case 'GUARD_HUNTED':
    $type = 'guard';
    $footer = 'guard_hunted';
    break;

  case 'REPORTER_SUCCESS':
    $type = 'reporter';
    $header = 'reporter_result_header';
    $footer = 'reporter_result_footer';
    break;

  case 'ANTI_VOODOO_SUCCESS':
    $type = 'guard';
    $footer = 'anti_voodoo_success';
    break;

  case 'POISON_CAT_RESULT':
    $type = 'mage';
    $footer = 'poison_cat_';
    break;

  case 'PHARMACIST_RESULT':
    $type = 'mage';
    $footer = 'pharmacist_';
    break;

  case 'ASSASSIN_RESULT':
    $type = 'mage';
    $header = 'assassin_result';
    break;

  case 'CLAIRVOYANCE_RESULT':
    $type = 'reporter';
    $header = 'clairvoyance_result_header';
    $footer = 'clairvoyance_result_footer';
    break;

  case 'TONGUE_WOLF_RESULT':
  case 'SEX_WOLF_RESULT':
    $type = 'mage';
    $header = 'wolf_result';
    break;

  case 'CHILD_FOX_RESULT':
    $type = 'mage';
    $header = 'mage_result';
    break;

  case 'FOX_EAT':
    $type = 'fox';
    $header = 'fox_targeted';
    break;

  case 'VAMPIRE_RESULT':
    $type = 'mage';
    $header = 'vampire_result';
    break;

  case 'MANIA_RESULT':
    $type = 'mage';
    break;

  case 'SYMPATHY_RESULT':
    $type = 'sympathy';
    $header = 'sympathy_result';
    break;

  case 'PRESAGE_RESULT':
    $type = 'reporter';
    $header = 'presage_result_header';
    $footer = 'reporter_result_footer';
    break;

  default:
    return false;
  }

  $target_date = $ROOM->date - 1;
  if($ROOM->test_mode){
    $stack = $RQ_ARGS->TestItems->system_message[$target_date][$action];
    $result_list = is_array($stack) ? $stack : array();
  }
  else{
    $query = 'SELECT DISTINCT message FROM system_message WHERE room_no = ' .
      "{$ROOM->id} AND date = {$target_date} AND type = '{$action}'";
    $result_list = FetchArray($query);
  }
  //PrintData($result_list);

  switch($type){
  case 'mage':
    foreach($result_list as $result){
      list($actor, $target, $data) = explode("\t", $result);
      if($SELF->IsSameName($actor)) OutputAbilityResult($header, $target, $footer . $data);
    }
    break;

  case 'necromancer':
    if(is_null($header)) $header = 'necromancer';
    foreach($result_list as $result){
      list($target, $data) = explode("\t", $result);
      OutputAbilityResult($header . '_result', $target, $footer . $data);
    }
    break;

  case 'priest':
    foreach($result_list as $result) OutputAbilityResult($header, $result, $footer);
    break;

  case 'crisis_priest':
    foreach($result_list as $result) OutputAbilityResult($header . $result, NULL, $footer);
    break;

  case 'guard':
    foreach($result_list as $result){
      list($actor, $target) = explode("\t", $result);
      if($SELF->IsSameName($actor)) OutputAbilityResult(NULL, $target, $footer);
    }
    break;

  case 'reporter':
    foreach($result_list as $result){
      list($actor, $target, $wolf) = explode("\t", $result);
      if($SELF->IsSameName($actor)){
	OutputAbilityResult($header, $target . ' さんは ' . $wolf, $footer);
      }
    }
    break;

  case 'fox':
    foreach($result_list as $result){
      if($SELF->IsSameName($result)) OutputAbilityResult($header, NULL);
    }
    break;

  case 'sympathy':
    foreach($result_list as $result){
      list($actor, $target, $data) = explode("\t", $result);
      if($SELF->IsSameName($actor) || $SELF->IsRole('ark_angel')){
	OutputAbilityResult($header, $target, $footer . $data);
      }
    }
    break;
  }
}

//能力発動結果を表示する
function OutputAbilityResult($header, $target, $footer = NULL){
  global $ROLE_IMG;

  $str = '<table class="ability-result"><tr>'."\n";
  if(isset($header)) $str .= $ROLE_IMG->Generate($header, NULL, true) ."\n";
  if(isset($target)) $str .= '<td>' . $target . '</td>'."\n";
  if(isset($footer)) $str .= $ROLE_IMG->Generate($footer, NULL, true) ."\n";
  echo $str . '</tr></table>'."\n";
}

//夜の未投票メッセージ出力
function OutputVoteMessage($class, $sentence, $situation, $not_situation = ''){
  global $MESSAGE, $ROOM, $USERS;

  if($ROOM->test_mode) return false; //テストモードならスキップ

  $stack = GetSelfVoteNight($situation, $not_situation);
  if(count($stack) < 1){
    $str = $MESSAGE->{'ability_' . $sentence};
  }
  elseif($situation == 'WOLF_EAT' || $situation == 'CUPID_DO'){
    $str = '投票済み';
  }
  elseif($not_situation != '' && $stack['situation'] == $not_situation){
    $str = 'キャンセル投票済み';
  }
  elseif($situation == 'POISON_CAT_DO' || $situation == 'POSSESSED_DO'){
    $str = $USERS->ByUname($stack['target_uname'])->handle_name . 'さんに投票済み';
  }
  else{
    $str = $USERS->GetHandleName($stack['target_uname'], true) . 'さんに投票済み';
  }
  echo '<span class="ability ' . $class . '">' . $str . '</span><br>'."\n";
}
