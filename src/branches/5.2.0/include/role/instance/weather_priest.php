<?php
/*
  ◆祈祷師 (weather_priest)
  ○仕様
  ・司祭：天候発動 (2日目以降)
*/
RoleLoader::LoadFile('priest');
class Role_weather_priest extends Role_priest {
  protected function IgnoreResult() {
    return DateBorder::PreTwo();
  }

  protected function IgnoreSetPriest() {
    if (false === DateBorder::OnThree()) {
      return true;
    }
    return false === DB::$USER->IsLiveRole($this->role);
  }

  protected function IgnorePriest() {
    //天変地異なら常時発動 > 3の倍数限定 > [生存者 - 村人陣営(恋人・愛人を含む) > 人狼系 × 2]
    if (DB::$ROOM->IsOption('full_weather')) {
      return false;
    } elseif (false === DateBorder::OnThree()) {
      return true;
    } else {
      $data = $this->GetStack('priest');
      return $data->count['total'] - $data->count['human_side'] <= $data->count['wolf'] * 2;
    }
  }

  protected function PriestAction() {
    $list = $this->GetWeatherList(); //天候発動リスト
    //試行テスト
    //$stack = []; for ($i = 0; $i < 20; $i++) @$stack[Lottery::Draw($list)]++;
    //ksort($stack); Text::p($stack, "◆{$this->role}");

    if (DB::$ROOM->IsOption('full_weather') && DateBorder::One()) { //天変地異対応
      DB::$ROOM->StoreWeather(Lottery::Draw($list), 1);
    }

    $weather = Lottery::Draw($list);
    //$weather = 44; //テスト用
    $date = 2;
    DB::$ROOM->StoreWeather($weather, $date, DB::$USER->IsLiveRole($this->role));
  }


  //天候発動リスト取得
  private function GetWeatherList(){
    $list = GameConfig::$weather_list;
    //Text::p($list, '◆WeatherList');

    $list = $this->CalibrationWeatherListCamp($list);
    //Text::p($list, '◆Calibration/Camp');

    $list = $this->CalibrationWeatherListRole($list);
    //Text::p($list, '◆Calibration/Role');

    return $list;
  }

  //天候発動リスト：陣営・投票余暇補正
  private function CalibrationWeatherListCamp(array $list) {
    $data = $this->GetStack('priest');

    //投票余暇取得
    $stack = $data->count;
    $vote_margin = ceil(($stack['total'] - 2) / 2) - $stack['wolf'] - $stack['fox'];
    //Text::p($vote_margin, '◆VoteMargin');

    if ($stack['fox'] > $stack['wolf']) { //妖狐陣営優勢
      foreach ([3, 8, 31, 36] as $id) {
	$list[$id] = ceil($list[$id] * 0.8);
      }
    }

    if ($vote_margin > 2) { //村人陣営優勢
      foreach ([17, 18, 20, 23, 30, 33, 35, 37, 41, 45, 47] as $id) {
	$list[$id] = ceil($list[$id] * 1.2);
      }
      foreach ([6, 7, 9, 16, 22, 32, 34, 46] as $id) {
	$list[$id] = ceil($list[$id] * 0.8);
      }
    } elseif ($vote_margin < 1) { //村人陣営劣勢
      foreach ([6, 7, 8, 9, 32, 34, 42, 46] as $id) {
	$list[$id] = ceil($list[$id] * 1.2);
      }
      foreach ([4, 5, 17, 18, 23, 33, 37, 39, 45, 47] as $id) {
	$list[$id] = ceil($list[$id] * 0.8);
      }
    }

    return $list;
  }

  //天候発動リスト：生存役職補正
  private function CalibrationWeatherListRole(array $list) {
    $off_list   = $this->GetCalibrationWeatherOffList();
    $role_list  = $this->GetCalibrationWeatherRoleList();
    $group_list = $this->GetCalibrationWeatherGroupList();

    foreach (DB::$USER->GetRole() as $role => $stack) {
      if (isset($off_list[$role])) {
	foreach ($off_list[$role] as $id) {
	  $list[$id] = 0;
	}
      }

      $calib_id = null;
      if (isset($role_list[$role])) {
	$calib_id = $role_list[$role];
      } else {
	foreach ($group_list as $group => $id) {
	  if (Text::Search($role, $group)) {
	    $calib_id = $id;
	    break;
	  }
	}
      }
      //Text::p($role, "◆WeatherCalib [{$calib_id}]");

      if (isset($calib_id)) {
	$count = 0;
	foreach ($stack as $id) {
	  if (DB::$USER->ByID($id)->IsLive(true)) {
	    $count++;
	  }
	}
	$list[$calib_id] = ceil($list[$calib_id] * (1 + $count * 0.1));
      }
    }

    return $list;
  }

  //天候発動補正リスト取得：発動抑制
  private function GetCalibrationWeatherOffList() {
    return [
      'detective_common' => [5, 15, 41]
    ];
  }

  //天候発動補正リスト取得：個別役職
  private function GetCalibrationWeatherRoleList() {
    return [
      'human'              => 24,
      'suspect'            => 42,
      'critical_mage'      =>  4,
      'bacchus_medium'     => 21,
      'critical_jealousy'  =>  4,
      'brownie'            => 24,
      'revive_brownie'     => 22,
      'cursed_brownie'     => 17,
      'mad'                => 60,
      'swindle_mad'        => 60,
      'jammer_mad'         => 36,
      'trap_mad'           => 37,
      'snow_trap_mad'      => 33,
      'corpse_courier_mad' => 45,
      'amaze_mad'          =>  2,
      'critical_mad'       =>  4,
      'follow_mad'         => 17,
      'critical_fox'       =>  4,
      'critical_avenger'   =>  4
    ];
  }

  //天候発動補正リスト取得：役職グループ
  private function GetCalibrationWeatherGroupList() {
    return [
      'cute'     => 42,
      'jeasouly' => 27,
      'depraver' =>  3,
      'vampire'  => 40,
      'fairy'    => 29
    ];
  }
}
