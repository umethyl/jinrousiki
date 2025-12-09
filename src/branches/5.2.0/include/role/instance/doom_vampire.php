<?php
/*
  ◆冥血鬼 (doom_vampire)
  ○仕様
  ・対吸血：無効
  ・吸血：死の宣告
  ・人狼襲撃耐性：無効
*/
RoleLoader::LoadFile('vampire');
class Role_doom_vampire extends Role_vampire {
  public function ResistWolfEat() {
    return true;
  }

  protected function InfectVampire(User $user) {
    return;
  }

  protected function InfectAction(User $user) {
    if (false === RoleUser::AvoidLovers($user, true)) {
      $user->AddDoom(4);
    }
  }
}
