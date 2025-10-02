<?php
/*
  ◆祈祷師 (weather_priest)
  ○仕様
  ・司祭：天候発動 (2日目以降)
*/
RoleManager::LoadFile('priest');
class Role_weather_priest extends Role_priest {
  protected function GetOutputRole() { return DB::$ROOM->date > 1 ? $this->role : null; }

  function Priest(StdClass $role_flag) {
    $data = $this->GetStack('priest');
    //スキップ判定
    if (! (isset($data->{$this->role}) ||
	   (DB::$ROOM->date > 2 && DB::$ROOM->date % 3 == 0 &&
	    $data->count['total'] - $data->count['human_side'] > $data->count['wolf'] * 2))) {
      return false;
    }

    //天候補正処理
    $vote_margin = ceil(($data->count['total'] - 2) / 2) -
      $data->count['wolf'] - $data->count['fox'];
    //Text::p($vote_margin, 'VoteMargin');

    $target = GameConfig::$weather_list;
    if ($data->count['fox'] > $data->count['wolf']) { //妖狐陣営優勢
      foreach (array(3, 8, 31, 36) as $id) {
	$target[$id] = ceil($target[$id] * 0.8);
      }
    }

    if ($vote_margin > 2) { //村人陣営優勢
      foreach (array(17, 18, 20, 23, 30, 33, 35, 37, 41, 45, 47) as $id) {
	$target[$id] = ceil($target[$id] * 1.2);
      }
      foreach (array(6, 7, 9, 16, 22, 32, 34, 46) as $id) {
	$target[$id] = ceil($target[$id] * 0.8);
      }
    }
    elseif ($vote_margin < 1) { //村人陣営劣勢
      foreach (array(6, 7, 8, 9, 32, 34, 42, 46) as $id) {
	$target[$id] = ceil($target[$id] * 1.2);
      }
      foreach (array(4, 5, 17, 18, 23, 33, 37, 39, 45, 47) as $id) {
	$target[$id] = ceil($target[$id] * 0.8);
      }
    }

    $stack = array(
      'human' => 24, 'suspect' => 42, 'bacchus_medium' => 21, 'brownie' => 24,
      'revive_brownie' => 22, 'cursed_brownie' => 17, 'jammer_mad' => 36, 'trap_mad' => 37,
      'snow_trap_mad' => 33, 'corpse_courier_mad' => 45, 'amaze_mad' => 2, 'critical_mad' => 4,
      'follow_mad' => 17, 'critical_avenger' => 4);
    foreach ($role_flag as $role => $list) {
      $id = null;
      if (array_key_exists($role, $stack)) {
	$id = $stack[$role];
      }
      elseif (strpos($role, 'cute') !== false) {
	$id = 42;
      }
      elseif (strpos($role, 'jealousy') !== false) {
	$id = 27;
      }
      elseif (strpos($role, 'vampire') !== false) {
	$id = 40;
      }
      elseif (strpos($role, 'fairy') !== false) {
	$id = 29;
      }
      //Text::p($role, $id);
      if (isset($id)) $target[$id] = ceil($target[$id] * (1 + count($list) * 0.1));
    }
    //Text::p($target);
    //$stack = array(); for ($i = 0; $i < 20; $i++) @$stack[Lottery::Draw($target)]++;
    //ksort($stack); Text::p($stack);
    $weather = Lottery::Draw($target);
    //$weather = 44; //テスト用
    $date = 2;
    $flag = isset($role_flag->{$this->role}) && count($role_flag->{$this->role}) > 0;
    DB::$ROOM->EntryWeather($weather, $date, $flag);
  }
}
