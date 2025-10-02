<?php
/*
  ◆野狐禅 (immolate_fox)
  ○仕様
  ・人狼襲撃カウンター：能力発現
  ・勝利：能力発現所持
*/
RoleLoader::LoadFile('fox');
class Role_immolate_fox extends Role_fox {
  public $mix_in = ['immolate_mad'];

  public function FoxEatCounter(User $user) {
    $this->AddMusterRole();
  }

  public function Win($winner) {
    return $this->IsMusterRole();
  }
}
