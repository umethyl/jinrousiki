<?php
/*
  ◆僵尸 (resurrect_mania)
  ○仕様
  ・人狼襲撃：確率蘇生 (コピー先生存時のみ)
*/
RoleManager::LoadFile('unknown_mania');
class Role_resurrect_mania extends Role_unknown_mania {
  public $mix_in = array('revive_pharmacist');

  public function IsResurrect() {
    $rate = DB::$ROOM->IsEvent('full_revive') ? 100 : 40;
    return $this->IsLivePartner() && Lottery::Percent($rate);
  }

  public function IsResurrectLost() {
    return false;
  }
}
