<?php
/*
  ◆冥狐 (doom_fox)
  ○仕様
  ・暗殺：死の宣告 (4日後)
*/
RoleManager::LoadFile('fox');
class Role_doom_fox extends Role_fox {
  public $mix_in = array('vote' => 'assassin');

  public function Assassin(User $user) {
    if ($user->IsLive(true)) $user->AddDoom(4, 'death_warrant');
  }
}
