<?php
/*
  ◆羊 (mind_sheep)
  ○仕様
  ・仲間表示：羊飼い (付加者)
  ・人狼襲撃：羊皮
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_sheep extends Role_mind_read {
  protected function GetPartner() {
    $stack = array();
    foreach ($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[$id] = DB::$USER->ByID($id)->handle_name;
    }
    ksort($stack);
    return array('shepherd_patron_list' => $stack);
  }

  public function WolfEatCounter(User $user) {
    $user->AddDoom(1, 'sheep_wisp');
  }
}
