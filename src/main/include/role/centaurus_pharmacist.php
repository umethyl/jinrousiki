<?php
/*
  ◆人馬 (centaurus_pharmacist)
  ○仕様
  ・処刑投票：毒死 (毒能力者限定)
*/
RoleManager::LoadFile('pharmacist');
class Role_centaurus_pharmacist extends Role_pharmacist {
  public $result = null;

  public function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      if ($this->DistinguishPoison(DB::$USER->ByRealUname($target_uname)) != 'nothing') {
	DB::$USER->Kill(DB::$USER->UnameToNumber($uname), 'POISON_DEAD');
      }
    }
  }
}
