<?php
/*
  ◆受信者 (mind_receiver)
  ○仕様
  ・仲間表示：受信先
  ・発言透過：受信先
*/
class Role_mind_receiver extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  protected function OutputPartner() {
    $stack = array();
    foreach($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[$id] = DB::$USER->ById($id)->handle_name;
    }
    ksort($stack);
    RoleHTML::OutputPartner($stack, 'mind_scanner_target');
  }

  function IsMindReadActive(User $user) {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $user->id);
  }
}
