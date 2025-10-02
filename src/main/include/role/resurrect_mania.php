<?php
/*
  ◆僵尸 (resurrect_mania)
  ○仕様
  ・人狼襲撃：確率蘇生 (コピー先生存時のみ)
*/
RoleLoader::LoadFile('unknown_mania');
class Role_resurrect_mania extends Role_unknown_mania {
  public $mix_in = array('revive_pharmacist');

  protected function IsResurrect() {
    $rate = DB::$ROOM->IsEvent('full_revive') ? 100 : 40;
    return $this->IsLivePartner() && Lottery::Percent($rate);
  }

  protected function IsResurrectLost() {
    return false;
  }
}
