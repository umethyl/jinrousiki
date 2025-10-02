<?php
/*
  ◆密偵 (emissary_necromancer)
  ○仕様
  ・霊能：処刑者の投票先と同陣営の人数
*/
RoleLoader::LoadFile('necromancer');
class Role_emissary_necromancer extends Role_necromancer {
  public $result = RoleAbility::EMISSARY_NECROMANCER;

  public function Necromancer(User $user, $flag){
    $camp  = $user->GetWinCamp();
    $count = 0;
    foreach ($this->GetVotedUname($user->uname) as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsWinCamp($camp)) {
	$count++;
      }
    }
    return $count;
  }
}
