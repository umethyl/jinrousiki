<?php
/*
  ◆羊 (mind_sheep)
  ○仕様
  ・人狼襲撃：羊皮
*/
class Role_mind_sheep extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  protected function OutputPartner() {
    $stack = array();
    foreach($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[$id] = DB::$USER->ById($id)->handle_name;
    }
    ksort($stack);
    RoleHTML::OutputPartner($stack, 'shepherd_patron_list');
  }

  function WolfEatCounter(User $user) { $user->AddDoom(1, 'sheep_wisp'); }
}
