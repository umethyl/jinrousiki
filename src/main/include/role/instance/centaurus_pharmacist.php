<?php
/*
  ◆人馬 (centaurus_pharmacist)
  ○仕様
  ・処刑投票：毒死 (毒能力者限定)
*/
RoleLoader::LoadFile('pharmacist');
class Role_centaurus_pharmacist extends Role_pharmacist {
  public $result = null;

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      if ($this->DistinguishPoison(DB::$USER->ByRealUname($target_uname)) != 'nothing') {
	DB::$USER->Kill(DB::$USER->UnameToNumber($uname), DeadReason::POISON_DEAD);
      }
    }
  }
}
