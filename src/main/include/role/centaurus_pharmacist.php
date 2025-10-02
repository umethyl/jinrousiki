<?php
/*
  ◆人馬 (centaurus_pharmacist)
  ○仕様
  ・処刑投票：投票先が毒を持っていたら死亡する
*/
RoleManager::LoadFile('pharmacist');
class Role_centaurus_pharmacist extends Role_pharmacist {
  protected function OutputResult() { return; }

  function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      if ($this->DistinguishPoison(DB::$USER->ByRealUname($target_uname)) != 'nothing') {
	DB::$USER->Kill(DB::$USER->UnameToNumber($uname), 'POISON_DEAD');
      }
    }
  }
}
