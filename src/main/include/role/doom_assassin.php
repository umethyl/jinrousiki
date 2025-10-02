<?php
/*
  ◆死神 (doom_assassin)
  ○仕様
  ・暗殺：死の宣告 (2日後)
*/
RoleManager::LoadFile('assassin');
class Role_doom_assassin extends Role_assassin {
  protected function SetAssassinTarget(User $user) { return; }

  protected function AssassinAction(User $user) { $user->AddDoom(2, 'death_warrant'); }
}
