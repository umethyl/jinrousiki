<?php
/*
  ◆僵尸 (resurrect_mania)
  ○仕様
  ・人狼襲撃：確率蘇生 (コピー先生存時のみ)
*/
RoleManager::LoadFile('unknown_mania');
class Role_resurrect_mania extends Role_unknown_mania {
  public $mix_in = 'revive_pharmacist';

  function Resurrect() {
    if ($this->IsResurrect($this->GetActor()) && $this->IsLivePartner() &&
	Lottery::Percent(DB::$ROOM->IsEvent('full_revive') ? 100 : 40)) {
      $this->GetActor()->Revive();
    }
  }
}
