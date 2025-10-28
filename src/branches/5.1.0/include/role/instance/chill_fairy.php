<?php
/*
  ◆狂妖精 (chill_fairy)
  ○仕様
  ・悪戯：サブ役職付加 (悪寒)
*/
RoleLoader::LoadFile('fairy');
class Role_chill_fairy extends Role_fairy {
  protected function FairyAction(User $user) {
    if (false === RoleUser::Avoid($user)) {
      $user->AddDoom(1, 'chill_febris');
    }
  }
}
