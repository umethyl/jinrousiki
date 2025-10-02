<?php
/*
  ◆羊 (mind_sheep)
  ○仕様
  ・表示：2 日目以降
  ・仲間表示：羊飼い (付加者)
  ・人狼襲撃：羊皮
*/
class Role_mind_sheep extends Role {
  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  protected function OutputPartner() {
    $stack = array();
    foreach ($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[$id] = DB::$USER->ByID($id)->handle_name;
    }
    ksort($stack);
    RoleHTML::OutputPartner($stack, 'shepherd_patron_list');
  }

  public function WolfEatCounter(User $user) {
    $user->AddDoom(1, 'sheep_wisp');
  }
}
