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

  public function GetCastRole($user_count) {
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
