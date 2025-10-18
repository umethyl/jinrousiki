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
      //未設定の場合は TypeA を入れておく
      $config = DuelConfig::$cast_list['a'];
      if (count($config) < 1) {
	return [];
      }
    }
    //Text::p($config, '◆Duel');

    //-- 初期化 --//
    $stack = [];

    //-- 固定枠 --//
    foreach ($config['fix'] as $role => $count) {
      $add_count = min($user_count - array_sum($stack), $count);
      if ($add_count > 0) {
	ArrayFilter::Add($stack, $role, $add_count);
      }
    }
    //Text::p($stack, '◆Duel[Fix]:');

    //-- 人口依存固定枠 --//
    $count_role_list = $config['count'];
    ksort($count_role_list); //人口境界昇順に補正をかける
    foreach ($count_role_list as $border => $role_list) {
      if ($user_count < $border) {
	break;
      }

      foreach ($role_list as $role => $count) {
	$add_count = min($user_count - array_sum($stack), $count);
	if ($add_count > 0) {
	  ArrayFilter::Add($stack, $role, $add_count);
	}
      }
    }
    //Text::p($stack, '◆Duel[Count]:');

    //-- 割合配分枠 --//
    $rate_role_list = $config['rate'];
    arsort($rate_role_list); //配分率降順で配役する
    $total_rate = array_sum($rate_role_list);
    $rest_count = $user_count - array_sum($stack);
    foreach ($rate_role_list as $role => $rate) {
      $count     = round($rest_count * ($rate / $total_rate));
      $add_count = min($user_count - array_sum($stack), $count);
      if ($add_count > 0) {
	ArrayFilter::Add($stack, $role, $add_count);
      }
    }

    //端数対応
    $add_count = $user_count - array_sum($stack);
    if ($add_count > 0) {
      $max_role = ArrayFilter::PickKey($rate_role_list); //最大確率の役職に加算する
      ArrayFilter::Add($stack, $max_role, $add_count);
    }
    //Text::p($stack, '◆Duel[Rate]:');

    //-- 補正枠 --//
    foreach ($config['calib'] as $role => $role_list) {
      if (ArrayFilter::Exists($stack, $role)) {
	foreach ($role_list as $replace_role => $border) {
	  if ($stack[$role] > $border) {
	    ArrayFilter::Add($stack, $role, -1);
	    ArrayFilter::Add($stack, $replace_role, 1);
	  }
	}
      }
    }
    //Text::p($stack, '◆Duel[Calix]:');

    return $stack;
  }
}
