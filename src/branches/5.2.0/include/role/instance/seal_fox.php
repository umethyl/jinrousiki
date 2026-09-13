<?php
/*
  ◆封狐 (seal_fox)
  ○仕様
  ・人狼襲撃耐性：無し
  ・人狼妖狐襲撃得票：抜牙
*/
RoleLoader::LoadFile('fox');
class Role_seal_fox extends Role_fox {
  public function ResistWolfEatFox() {
    return false;
  }

  public function WolfEatFoxReaction(User $user) {
    $user->AddRole('lost_fang');
  }
}
