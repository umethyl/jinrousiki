<?php
/*
  ◆おしら様 (select_assassin)
  ○仕様
  ・暗殺：オシラ遊び付加
*/
RoleLoader::LoadFile('assassin');
class Role_select_assassin extends Role_assassin {
  protected function IsAssassinKill() {
    return false;
  }

  protected function AssassinAction(User $user) {
    $user->AddDoom(1, 'death_selected');
  }
}
