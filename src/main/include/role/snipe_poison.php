<?php
/*
  ◆狙毒者 (snipe_poison)
  ○仕様
  ・毒：処刑投票先と同陣営 (恋人は恋人陣営)
*/
RoleLoader::LoadFile('poison');
class Role_snipe_poison extends Role_poison {
  protected function IsPoisonTarget(User $user) {
    $target = $this->GetVoteKillUser();
    $camp   = $target->GetWinCamp();
    return $user->IsWinCamp($camp) && false === RoleUser::IsAvoidLovers($target, true);
  }
}
