<?php
/*
  ◆闇鍋モード (chaos)
  ・配役：専用設定
  ・闇鍋モード配役補正：有効
*/
class Option_chaos extends OptionCastCheckbox {
  public function GetCaption() {
    return '闇鍋モード';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }

  public function GetCastRole($user_count) {
    //Text::p($this->name, '◆Chaos Type');

    //-- 固定枠設定 --//
    $fix_role_list = ChaosConfig::${$this->name . '_fix_role_list'}; //個別設定
    OptionManager::FilterCastChaosFixRole($fix_role_list, $user_count);
    //Text::p($fix_role_list, sprintf('◆Fix(%d)', array_sum($fix_role_list)));

    //-- ランダム枠決定 --//
    $random_role_list = []; //ランダム配役結果
    $boost_list = OptionManager::GetCastChaosBoostRole();
    //Text::p($boost_list, '◆boost');

    //-- 最小出現補正 --//
    if (true === $this->EnableCastChaosCalibration()) {
      $stack = []; //役職系統別配役数
      foreach ($fix_role_list as $role => $count) { //固定枠を系統別にカウント
	ArrayFilter::Add($stack, RoleDataManager::GetGroup($role), $count);
      }
      //Text::p($stack, '◆Min: Fix: Group');

      foreach (['wolf', 'fox'] as $role) {
	$name  = ChaosConfig::${sprintf('%s_%s_list', $this->name, $role)};
	$min   = ChaosConfig::${sprintf('min_%s_rate', $role)};
	$rate  = Lottery::GetChaos($name, $boost_list);
	$list  = Lottery::Generate($rate);
	$count = round($user_count / $min) - ArrayFilter::GetInt($stack, $role);
	Lottery::Add($random_role_list, $list, $count);
	//Lottery::ToProbability($rate); //テスト用
	//Text::p($list, "◆Min [{$count}]");
	//Text::p($random_role_list, "◆Min [{$role}]");
      }
    }
    //Text::p($random_role_list, sprintf('◆Min: Random(%d)', array_sum($random_role_list)));

    //-- ランダム配役 --//
    $name  = ChaosConfig::${$this->name . '_random_role_list'};
    $rate  = Lottery::GetChaos($name, $boost_list);
    $list  = Lottery::Generate($rate);
    $count = $user_count - (array_sum($random_role_list) + array_sum($fix_role_list));
    Lottery::Add($random_role_list, $list, $count);
    //Lottery::ToProbability($rate); //テスト用
    //Text::p(array_sum($rate), '◆Random: Total');
    //Text::p($list, "◆Random [{$count}]");

    //-- 補正処理 --//
    //固定とランダムを合計
    $role_list = $random_role_list;
    foreach ($fix_role_list as $role => $count) {
      ArrayFilter::Add($role_list, $role, $count);
    }
    //Text::p($role_list, sprintf('◆1st(%d)', array_sum($role_list)));

    //-- 上限補正 --//
    if (true === $this->EnableCastChaosCalibration()) {
      //役職グループ毎に集計
      $total_stack  = []; //グループ別リスト (全配役)
      $random_stack = []; //グループ別リスト (ランダム)
      foreach ($role_list as $role => $count) {
	$total_stack[RoleDataManager::GetGroup($role)][$role] = $count;
      }
      foreach ($random_role_list as $role => $count) {
	$random_stack[RoleDataManager::GetGroup($role)][$role] = $count;
      }

      foreach (ChaosConfig::$role_group_rate_list as $group => $rate) {
	if (false === ArrayFilter::IsAssoc($random_stack, $group)) {
	  continue;
	}

	$target = $random_stack[$group];
	$count  = array_sum($total_stack[$group]) - round($user_count / $rate);
	//if ($count > 0) Text::p($count, "◆Calib [{$group}]"); //テスト用
	for (; $count > 0; $count--) {
	  if (array_sum($target) < 1) {
	    break;
	  }
	  //Text::p($target, sprintf('◆　　%d: before', $count));
	  arsort($target);
	  //Text::p($target, sprintf('◆　　%d: afetr', $count));
	  $key = key($target);
	  //Text::p($key, '◆　　target');
	  $target[$key]--;
	  ArrayFilter::Replace($role_list, $key, 'human');
	  //Text::p($target, sprintf('◆　　%d: delete', $count));

	  //0 になった役職はリストから除く
	  ArrayFilter::Sweep($role_list, $key);
	  ArrayFilter::Sweep($target, $key);
	}
      }
      //Text::p($role_list, sprintf('◆2nd(%d)', array_sum($role_list)));
    }

    //-- 身代わり君モード補正 --//
    if (DB::$ROOM->IsDummyBoy()) {
      $dummy_count   = $user_count; //身代わり君対象役職数
      $target_stack  = []; //補正対象リスト
      $disable_stack = Cast::GetDisableCastDummyBoyRoleList(); //身代わり君の配役対象外役職リスト
      foreach ($role_list as $role => $count) { //対象役職の情報を収集
	foreach ($disable_stack as $disable_role) {
	  if (Text::Search($role, $disable_role)) {
	    $target_stack[$disable_role][$role] = $count;
	    $dummy_count -= $count;
	    break; //多重カウント防止 (例：poison_wolf)
	  }
	}
      }

      if ($dummy_count < 1) {
	//Text::p($target_stack, '◆Dummy');
	foreach ($target_stack as $role => $stack) { //対象役職からランダムに村人へ置換
	  //Text::p($stack, "◆　　$role");
	  //人狼・探偵村の探偵はゼロにしない
	  if (($role == 'wolf' || (DB::$ROOM->IsOption('detective') && $role == 'detective')) &&
	      array_sum($stack) < 2) {
	    continue;
	  }

	  arsort($stack);
	  //Text::p($stack, "◆　　list");
	  $key = key($stack);
	  //Text::p($key, "◆　　role");
	  ArrayFilter::Replace($role_list, $key, 'human');
	  ArrayFilter::Sweep($role_list, $key); //0 になった役職はリストから除く
	  break;
	}
	//Text::p($role_list, sprintf('◆3rd(%d)', array_sum($role_list)));
      }
    }

    //-- 村人上限補正 --//
    if (true === $this->EnableCastChaosCalibration() &&
	false === OptionManager::ExistsReplaceHuman() &&
	ArrayFilter::Exists($role_list, 'human')) {
      $role  = 'human';
      $count = $role_list[$role] - round($user_count / ChaosConfig::$max_human_rate);

      //ゲルト君モード補正
      if (OptionManager::EnableGerd()) {
	$count--;
      }
      //Text::p($count, '◆Human Count Limit');

      if ($count > 0) {
	$name = ChaosConfig::${$this->name . '_replace_human_role_list'};
	$rate = Lottery::GetChaos($name, $boost_list);
	$list = Lottery::Generate($rate);
	Lottery::Add($role_list, $list, $count);
	//Lottery::ToProbability($rate); //テスト用
	//Text::p($list, "◆Human [{$count}]");
	$role_list[$role] -= $count;
	ArrayFilter::Sweep($role_list, $role); //0 になったらリストから除く
	//Text::p($role_list, sprintf('◆4th(%d)', array_sum($role_list)));
      }
    }

    return $role_list;
  }

  //闇鍋モード配役補正有効判定
  protected function EnableCastChaosCalibration() {
    return true;
  }
}
