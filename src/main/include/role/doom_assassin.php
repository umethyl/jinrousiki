<?php
/*
  ◆死神 (doom_assassin)
  ○仕様
  ・暗殺：死の宣告 (2日後)
*/
RoleLoader::LoadFile('assassin');
class Role_doom_assassin extends Role_assassin {
  protected function IsAssassinKill() {
    return false;
  }

  protected function AssassinAction(User $user) {
    $user->AddDoom($this->CallParent('GetDoomAssassinDate'), 'death_warrant');
  }

  //死の宣告日数取得
  protected function GetDoomAssassinDate() {
    return 2;
  }
}
