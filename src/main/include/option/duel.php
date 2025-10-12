<?php
/*
  ◆決闘村 (duel)
  ○仕様
  ・配役：専用設定 (CastConfig)
*/
class Option_duel extends OptionCastCheckbox {
  public function GetCaption() {
    return '決闘村';
  }

  protected function IgnoreImage() {
    //決闘種別が取れる場合は移譲する
    return ArrayFilter::Exists(RoomOption::$stack, 'duel_selector');
  }

  protected function IgnoreRoomCaption() {
    return $this->IgnoreImage();
  }

  public function GetCastRole($user_count) {
    //-- 設定取得 --//
    $config = DB::$ROOM->GetDuelOptionList('duel_selector');
    if (count($config) < 1) {
      return [];
    }
    //Text::p($config, '◆Duel');

    //-- 初期化 --//
    $stack = [];

    //-- 固定枠 --//
    $fix_role_list = $config['fix'];
    if ($user_count >= array_sum($fix_role_list)) {
      foreach ($fix_role_list as $role => $count) {
	$stack[$role] = $count;
      }
    }

    //-- 人口依存固定枠 --//
    $count_role_list = $config['count'];
    foreach ($count_role_list as $border => $list) {
      if ($user_count >= $border && $user_count >= array_sum($stack) + array_sum($list)) {
	foreach ($list as $role => $count) {
	  $stack[$role] = $count;
	}
      } else {
	break;
      }
    }

    //-- 割合配分枠 --//
    $rate_role_list = $config['rate'];
    asort($rate_role_list);
    $max_role   = ArrayFilter::PopKey($rate_role_list); //最大確率の役職
    $total_rate = array_sum($rate_role_list);
    $rest_count = $user_count - array_sum($stack);
    foreach ($rate_role_list as $role => $rate) {
      if ($role != $max_role) {
	$stack[$role] = round($rest_count / $total_rate * $rate);
      }
    }
    $stack[$max_role] = $user_count - array_sum($stack); //端数対策

    //-- 補正枠 --//
    $calib_role_list = $config['calib'];
    foreach ($calib_role_list as $role => $list) {
      if (ArrayFilter::Exists($stack, $role)) {
	foreach ($list as $replace_role => $border) {
	  if ($stack[$role] > $border) {
	    ArrayFilter::Add($stack, $role, -1);
	    ArrayFilter::Add($stack, $replace_role, 1);
	  }
	}
      }
    }

    return $stack;
  }

  public function xGetCastRole($user_count) {
    CastConfig::InitializeDuel($user_count);

    $stack = [];
    if ($user_count >= array_sum(CastConfig::$duel_fix_list)) {
      foreach (CastConfig::$duel_fix_list as $role => $count) {
	$stack[$role] = $count;
      }
    }

    asort(CastConfig::$duel_rate_list);
    $max_role   = ArrayFilter::PopKey(CastConfig::$duel_rate_list); //最大確率の役職
    $total_rate = array_sum(CastConfig::$duel_rate_list);
    $rest_count = $user_count - array_sum($stack);
    foreach (CastConfig::$duel_rate_list as $role => $rate) {
      if ($role != $max_role) {
	$stack[$role] = round($rest_count / $total_rate * $rate);
      }
    }
    $stack[$max_role] = $user_count - array_sum($stack); //端数対策

    CastConfig::FinalizeDuel($user_count, $stack);
    return $stack;
  }
}
