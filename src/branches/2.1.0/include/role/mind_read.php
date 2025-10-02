<?php
/*
  ◆サトラレ (mind_read)
  ○仕様
*/
class Role_mind_read extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  function IsMindRead() {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $this->GetViewer()->user_no) &&
      ! $this->GetActor()->IsRole('unconscious');
  }
}
