<?php
//-- 配役基礎クラス --//
class Cast {
  //人数とゲームオプションに応じた役職テーブルを返す
  static function GetRoleList($user_count) {
    $error_header = 'ゲームスタート[配役設定エラー]：';
    $error_footer = '。<br>管理者に問い合わせて下さい。';

    $role_list = CastConfig::$role_list[$user_count]; //人数に応じた配役リストを取得
    if (is_null($role_list)) { //リストの有無をチェック
      $str = $user_count . '人は設定されていません';
      VoteHTML::OutputResult($error_header . $str . $error_footer, true);
    }
    //Text::p(DB::$ROOM->option_list);

    if (DB::$ROOM->IsOptionGroup('chaos')) { //闇鍋モード
      $random_role_list = array(); //ランダム配役結果
      foreach (array('chaos', 'chaosfull', 'chaos_hyper', 'chaos_verso') as $option) { //種別検出
	if (DB::$ROOM->IsOption($option)) {
	  $base_name   = $option;
	  $chaos_verso = $option == 'chaos_verso';
	  break;
	}
      }

      //-- 固定枠設定 --//
      $fix_role_list = ChaosConfig::${$base_name . '_fix_role_list'}; //個別設定

      if (count($stack = DB::$ROOM->GetOptionList('topping')) > 0) { //固定配役追加モード
	//Text::p($stack, 'topping');
	if (is_array(@$stack['fix'])) { //定数
	  foreach ($stack['fix'] as $role => $count) {
	    @$fix_role_list[$role] += $count;
	  }
	}
	if (is_array(@$stack['random'])) { //ランダム
	  foreach ($stack['random'] as $key => $list) {
	    $random_list = Lottery::Generate($list);
	    //Text::p($random_list, $stack['count'][$key]);
	    for ($count = $stack['count'][$key]; $count > 0; $count--) {
	      @$fix_role_list[Lottery::Get($random_list)]++;
	    }
	  }
	}
	//Text::p($fix_role_list, sprintf('Topping(%d)', array_sum($fix_role_list)));
      }

      //個別オプション(ゲルト君モード：村人 / 探偵村：探偵)
      foreach (array('gerd' => 'human', 'detective' => 'detective_common') as $option => $role) {
	if (DB::$ROOM->IsOption($option) && ! isset($fix_role_list[$role])) {
	  $fix_role_list[$role] = 1;
	}
      }
      //Text::p($fix_role_list, sprintf('Fix(%d)', array_sum($fix_role_list)));

      $boost_list = DB::$ROOM->GetOptionList('boost_rate'); //出現率補正リスト
      //Text::p($boost_list, 'boost');
      if (! $chaos_verso) { //-- 最小出現補正 --//
	$stack = array(); //役職系統別配役数
	foreach ($fix_role_list as $key => $value) { //固定枠内の該当グループをカウント
	  @$stack[RoleData::DistinguishRoleGroup($key)] += $value;
	}
	//Text::p($stack, 'FixRole');

	foreach (array('wolf', 'fox') as $role) {
	  $name  = ChaosConfig::${sprintf('%s_%s_list', $base_name, $role)};
	  $rate  = Lottery::GetChaos($name, $boost_list);
	  $list  = Lottery::Generate($rate);
	  $base  = isset($stack[$role]) ? $stack[$role] : 0;
	  $min   = ChaosConfig::${sprintf('min_%s_rate', $role)};
	  $count = round($user_count / $min) - $base;
	  //Text::p($list, $count);
	  //Lottery::ToProbability($rate); //テスト用
	  Lottery::Add($random_role_list, $list, $count);
	  //Text::p($random_role_list, $role);
	}
      }
      //Text::p($random_role_list, sprintf('random(%d)', array_sum($random_role_list)));

      //-- ランダム配役 --//
      $name  = ChaosConfig::${$base_name . '_random_role_list'};
      $rate  = Lottery::GetChaos($name, $boost_list);
      $list  = Lottery::Generate($rate);
      $count = $user_count - (array_sum($random_role_list) + array_sum($fix_role_list));
      //Text::p($list, $count);
      //Text::p(array_sum($rate));
      //Lottery::ToProbability($rate); //テスト用
      Lottery::Add($random_role_list, $list, $count);

      //固定とランダムを合計
      $role_list = $random_role_list;
      foreach ($fix_role_list as $key => $value) @$role_list[$key] += (int)$value;
      //Text::p($role_list, sprintf('1st(%d)', array_sum($role_list)));

      if (! $chaos_verso) { //-- 上限補正 --//
	//役職グループ毎に集計
	$total_stack  = array(); //グループ別リスト (全配役)
	$random_stack = array(); //グループ別リスト (ランダム)
	foreach ($role_list as $role => $count) {
	  $total_stack[RoleData::DistinguishRoleGroup($role)][$role] = $count;
	}
	foreach ($random_role_list as $role => $count) {
	  $random_stack[RoleData::DistinguishRoleGroup($role)][$role] = $count;
	}

	foreach (ChaosConfig::$role_group_rate_list as $name => $rate) {
	  $target = @$random_stack[$name];
	  if (! (is_array(@$total_stack[$name]) && is_array($target))) continue;
	  $count = array_sum($total_stack[$name]) - round($user_count / $rate);
	  //if ($count > 0) Text::p($count, $name); //テスト用
	  for (; $count > 0; $count--) {
	    if (array_sum($target) < 1) break;
	    //Text::p($target, sprintf('　　%d: before', $count));
	    arsort($target);
	    //Text::p($target, sprintf('　　%d: afetr', $count));
	    $key = key($target);
	    //Text::p($key, '　　target');
	    $target[$key]--;
	    $role_list[$key]--;
	    isset($role_list['human']) ? $role_list['human']++ : $role_list['human'] = 1;
	    //Text::p($target, sprintf('　　%d: delete', $count));

	    //0 になった役職はリストから除く
	    if ($role_list[$key] < 1) unset($role_list[$key]);
	    if ($target[$key]    < 1) unset($target[$key]);
	  }
	}
	//Text::p($role_list, sprintf('2nd(%d)', array_sum($role_list)));
      }

      if (DB::$ROOM->IsDummyBoy()) { //-- 身代わり君モード補正 --//
	$dummy_count   = $user_count; //身代わり君対象役職数
	$target_stack  = array(); //補正対象リスト
	$disable_stack = self::GetDummyBoyRoleList(); //身代わり君の対象外役職リスト
	foreach ($role_list as $role => $count) { //対象役職の情報を収集
	  foreach ($disable_stack as $disable_role) {
	    if (strpos($role, $disable_role) !== false) {
	      $target_stack[$disable_role][$role] = $count;
	      $dummy_count -= $count;
	      break; //多重カウント防止 (例：poison_wolf)
	    }
	  }
	}

	if ($dummy_count < 1) {
	  //Text::p($target_stack, "for dummy");
	  foreach ($target_stack as $role => $stack) { //対象役職からランダムに村人へ置換
	    //Text::p($stack, "　　$role");
	    //人狼・探偵村の探偵はゼロにしない
	    if (($role == 'wolf' || (DB::$ROOM->IsOption('detective') && $role == 'detective')) &&
		array_sum($stack) < 2) continue;

	    arsort($stack);
	    //Text::p($stack, "　　list");
	    $key = key($stack);
	    //Text::p($key, "　　role");
	    $role_list[$key]--;
	    $role_list['human']++;
	    if ($role_list[$key] < 1) unset($role_list[$key]); //0 になった役職はリストから除く
	    break;
	  }
	  //Text::p($role_list, '3rd_list('.array_sum($role_list).')');
	}
      }

      if (! $chaos_verso && ! DB::$ROOM->IsReplaceHumanGroup()) { //-- 村人上限補正 --//
	$role  = 'human';
	$count = @(int)$role_list[$role] - round($user_count / ChaosConfig::$max_human_rate);
	if (DB::$ROOM->IsOption('gerd')) $count--;
	if ($count > 0) {
	  $name = ChaosConfig::${$base_name . '_replace_human_role_list'};
	  $rate = Lottery::GetChaos($name, $boost_list);
	  $list = Lottery::Generate($rate);
	  //Text::p($list, $count);
	  //Lottery::ToProbability($rate); //テスト用
	  Lottery::Add($role_list, $list, $count);
	  $role_list[$role] -= $count;
	  if ($role_list[$role] < 1) unset($role_list[$role]); //0 になったらリストから除く
	  //Text::p($role_list, sprintf('4th_list(%d)', array_sum($role_list)));
	}
      }
    }
    elseif (DB::$ROOM->IsOption('duel')) { //決闘村
      $role_list = self::SetDuel($user_count);
    }
    elseif (DB::$ROOM->IsOption('gray_random')) { //グレラン村
      $role_list = self::SetGrayRandom($user_count);
    }
    elseif (DB::$ROOM->IsQuiz()) { //クイズ村
      $role_list = self::SetQuiz($user_count);
    }
    else { //通常村
      OptionManager::SetRole($role_list, $user_count);
    }
    self::ReplaceRole($role_list); //村人置換村

    //お祭り村
    if (DB::$ROOM->IsOption('festival') &&
	is_array($target = CastConfig::$festival_role_list[$user_count])) {
      $role_list = $target;
    }

    if (@$role_list['human'] < 0) { //村人の人数をチェック
      $str = '「村人」の人数がマイナスになってます';
      VoteHTML::OutputResult($error_header . $str . $error_footer, true);
    }
    if (@$role_list['wolf'] < 0) { //人狼の人数をチェック
      $str = '「人狼」の人数がマイナスになってます';
      VoteHTML::OutputResult($error_header . $str . $error_footer, true);
    }

    //役職名を格納した配列を生成
    $now_role_list = array();
    foreach ($role_list as $key => $value) {
      for ($i = 0; $i < $value; $i++) $now_role_list[] = $key;
    }
    $role_count = count($now_role_list);

    if ($role_count != $user_count) { //配列長をチェック
      if (DB::$ROOM->test_mode) {
	Text::p($role_count, 'エラー：配役数');
	return $now_role_list;
      }
      $str = sprintf('村人 (%d) と配役の数 (%d) が一致していません', $user_count, $role_count);
      VoteHTML::OutputResult($error_header . $str . $error_footer, true);
    }

    return $now_role_list;
  }

  //身代わり君の配役処理
  static function SetDummyBoy(array &$fix_role_list, array &$role_list) {
    //役職固定オプション判定
    $fix_role = null;
    if (DB::$ROOM->IsOption('gerd') && in_array('human', $role_list)) {
      $fix_role = 'human';
    }
    elseif (DB::$ROOM->IsQuiz()) {
      $fix_role = 'quiz';
    }

    if (isset($fix_role)) {
      if (($key = array_search($fix_role, $role_list)) !== false) {
	$fix_role_list[] = $fix_role;
	unset($role_list[$key]);
      }
      return;
    }

    shuffle($role_list); //配列をシャッフル
    $stack = self::GetDummyBoyRoleList(); //身代わり君の対象外役職リスト
    for ($i = count($role_list); $i > 0; $i--) {
      $role = array_shift($role_list); //配役リストから先頭を抜き出す
      foreach ($stack as $disable_role) {
	if (strpos($role, $disable_role) !== false) {
	  $role_list[] = $role; //配役リストの末尾に戻す
	  continue 2;
	}
      }
      $fix_role_list[] = $role;
      break;
    }
  }

  static function SetSubRole(array &$fix_role_list) {
    $rand_keys = array_keys($fix_role_list); //人数分の ID リストを取得
    shuffle($rand_keys); //シャッフルしてランダムキーに変換
    //Text::p($rand_keys, 'rand_keys');

    OptionManager::$stack = RoleManager::$disable_cast_list; //割り振り対象外役職のリスト
    //サブ役職テスト用
    /*
    $stack = array('wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp');
    foreach ($stack as $role) {
      while (count($rand_keys) > 0) {
	$id = array_shift($rand_keys);
	if ($fix_uname_list[$id] == 'dummy_boy') {
	  $rand_keys[] = $id;
	  if (count($rand_keys) == 1) break;
	  continue;
	}
	OptionManager::$stack[] = $role;
	$fix_role_list[$id] .= ' ' . $role;
	break;
      }
    }
    */
    OptionManager::Cast($fix_role_list, $rand_keys);

    //闇鍋モード処理
    if (DB::$ROOM->IsOption('no_sub_role') || ! DB::$ROOM->IsOptionGroup('chaos')) return;

    //ランダムなサブ役職のコードリストを作成
    if (DB::$ROOM->IsOption('sub_role_limit_easy')) {
      $sub_role_keys = ChaosConfig::$chaos_sub_role_limit_easy_list;
    }
    elseif (DB::$ROOM->IsOption('sub_role_limit_normal')) {
      $sub_role_keys = ChaosConfig::$chaos_sub_role_limit_normal_list;
    }
    elseif (DB::$ROOM->IsOption('sub_role_limit_hard')) {
      $sub_role_keys = ChaosConfig::$chaos_sub_role_limit_hard_list;
    }
    else {
      $sub_role_keys = array_keys(RoleData::$sub_role_list);
    }
    //Text::p(OptionManager::$stack, 'DeleteRoleList');

    $sub_role_keys = array_diff($sub_role_keys, OptionManager::$stack);
    //Text::p($sub_role_keys, 'SubRoleList');
    shuffle($sub_role_keys);
    foreach ($rand_keys as $id) {
      $fix_role_list[$id] .= ' ' . array_pop($sub_role_keys);
    }
  }

  //決闘村の配役処理
  static function SetDuel($user_count) {
    CastConfig::InitializeDuel($user_count);

    $stack = array();
    if (array_sum(CastConfig::$duel_fix_list) <= $user_count) {
      foreach (CastConfig::$duel_fix_list as $role => $count) {
	$stack[$role] = $count;
      }
    }

    asort(CastConfig::$duel_rate_list);
    $max_role   = array_pop(array_keys(CastConfig::$duel_rate_list)); //最大確率の役職
    $total_rate = array_sum(CastConfig::$duel_rate_list);
    $rest_count = $user_count - array_sum($stack);
    foreach (CastConfig::$duel_rate_list as $role => $rate) {
      if ($role != $max_role) $stack[$role] = round($rest_count / $total_rate * $rate);
    }
    $stack[$max_role] = $user_count - array_sum($stack); //端数対策

    CastConfig::FinalizeDuel($user_count, $stack);
    return $stack;
  }

  //クイズ村の配役処理
  static function SetQuiz($count) {
    $stack = self::FilterRole($count, array('common', 'wolf', 'mad', 'fox'));
    $stack['human']--;
    $stack['quiz'] = 1;
    return $stack;
  }

  //グレラン村の配役処理
  static function SetGrayRandom($count) {
    return self::FilterRole($count, array('wolf', 'mad', 'fox'));
  }

  //村人置換村の処理
  static function ReplaceRole(array &$list) {
    $stack = array();
    foreach (array_keys(DB::$ROOM->option_role->list) as $option) { //処理順にオプションを登録
      if ($option == 'replace_human' || strpos($option, 'full_') === 0) {
	$stack[0][] = $option;
      }
      elseif (strpos($option, 'change_') === 0) {
	$stack[1][] = $option;
      }
    }

    foreach ($stack as $order => $option_list) {
      foreach ($option_list as $option) {
	if (array_key_exists($option, CastConfig::$replace_role_list)) { //管理者設定
	  $target = CastConfig::$replace_role_list[$option];
	  $role   = array_pop(explode('_', $option));
	}
	elseif ($order == 0) { //村人置換
	  $target = array_pop(explode('_', $option, 2));
	  $role   = 'human';
	}
	else { //共有者・狂人・キューピッド置換
	  $target = array_pop(explode('_', $option, 2));
	  $role   = $target == 'angel' ? 'cupid' : array_pop(explode('_', $target));
	}

	$count = isset($list[$role]) ? $list[$role] : 0;
	if ($role == 'human' && DB::$ROOM->IsOption('gerd')) $count--; //ゲルト君モード
	if ($count > 0) { //置換処理
	  isset($list[$target]) ? $list[$target] += $count : $list[$target] = $count;
	  $list[$role] -= $count;
	}
      }
    }
  }

  //身代わり君の配役対象外役職リスト取得
  private function GetDummyBoyRoleList() {
    $stack = CastConfig::$disable_dummy_boy_role_list; //サーバ個別設定を取得
    array_push($stack, 'wolf', 'fox'); //常時対象外の役職を追加

    //探偵村対応
    $role = 'detective_common';
    if (DB::$ROOM->IsOption('detective') && ! in_array($role, $stack)) $stack[] = $role;
    return $stack;
  }

  //配役フィルタリング処理
  private function FilterRole($count, array $filter) {
    $stack = array();
    foreach (CastConfig::$role_list[$count] as $key => $value) {
      $role = 'human';
      foreach ($filter as $set_role) {
	if (strpos($key, $set_role) !== false) {
	  $role = $set_role;
	  break;
	}
      }
      isset($stack[$role]) ? $stack[$role] += $value : $stack[$role] = $value;
    }
    return $stack;
  }
}
