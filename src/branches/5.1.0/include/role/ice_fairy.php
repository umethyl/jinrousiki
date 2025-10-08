<?php
/*
  ◆氷妖精 (ice_fairy)
  ○仕様
  ・悪戯：サブ役職付加 (凍傷 / 30% で反射)
*/
RoleLoader::LoadFile('fairy');
class Role_ice_fairy extends Role_fairy {
  protected function FairyAction(User $user) {
    if (RoleUser::IsAvoidLovers($user, true) || Lottery::Percent(30)) {
      $target = $this->GetActor();
    } else {
      $target = $user;
    }
    $target->AddDoom(1, 'frostbite');
  }
}
