<?php
/*
  ◆茨木童子 (revive_ogre)
  ○仕様
  ・勝利：生存 + 嘘吐き全滅
  ・人攫い成功率低下：1/2
  ・人狼襲撃：確率蘇生 (無効確率 0%)
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('ogre');
class Role_revive_ogre extends Role_ogre {
  public $mix_in = ['psycho_mage', 'revive_pharmacist'];

  protected function GetOgreResistWolfEatRate() {
    return 0;
  }

  public function GetReflectAssassinRate() {
    return 40;
  }

  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IsResurrect() {
    $event = $this->GetOgreEvent();
    return Lottery::Percent(is_null($event) ? 40 : $event);
  }

  protected function IsResurrectLost() {
    return false;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $this->IsLiar($user);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
