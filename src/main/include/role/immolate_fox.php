<?php
/*
  ◆野狐禅 (immolate_fox)
  ○仕様
  ・人狼襲撃カウンター：能力発現
  ・勝利：能力発現所持
*/
RoleManager::LoadFile('fox');
class Role_immolate_fox extends Role_fox {
  public $ability = 'muster_ability';

  public function FoxEatCounter(User $user) {
    $this->GetActor()->AddRole($this->ability);
  }

  public function Win($winner) {
    return $this->GetActor()->IsRole($this->ability);
  }
}
