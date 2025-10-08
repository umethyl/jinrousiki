<?php
/*
  ◆影武者 (sacrifice_mania)
  ○仕様
  ・追加役職：庇護者
  ・人狼襲撃耐性：常時無効
*/
RoleLoader::LoadFile('unknown_mania');
class Role_sacrifice_mania extends Role_unknown_mania {
  protected function GetCopyRole(User $user) {
    return $user->GetID('protected');
  }

  public function ResistWolfEat() {
    return true;
  }
}
