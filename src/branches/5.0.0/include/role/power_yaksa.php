<?php
/*
  ◆阿修羅 (power_yaksa)
  ○仕様
  ・勝利：生存 + 生存陣営数が出現陣営の半分以下
  ・人攫い成功率低下：3/5
  ・人攫い無効：村人陣営
  ・人狼襲撃無効確率：30%
  ・暗殺反射確率：30%
*/
RoleLoader::LoadFile('yaksa');
class Role_power_yaksa extends Role_yaksa {
  protected function GetOgreResistWolfEatRate() {
    return 30;
  }

  public function GetReflectAssassinRate() {
    return 30;
  }

  protected function IgnoreSetOgreAssassin(User $user) {
    return $user->IsWinCamp(Camp::HUMAN);
  }

  protected function GetOgreReduceNumerator() {
    return 3;
  }

  protected function IsOgreLoseCamp($winner) {
    return false;
  }

  protected function IgnoreOgreLoseSurvive() {
    return true;
  }

  protected function OgreWin() {
    $camp_list = [];
    $live_list = [];
    foreach (DB::$USER->Get() as $user) {
      $camp = $user->GetWinCamp();
      $camp_list[$camp] = true;
      if ($user->IsLive()) {
	$live_list[$camp] = true;
      }
    }
    return count($live_list) <= ceil(count($camp_list) / 2);
  }
}
