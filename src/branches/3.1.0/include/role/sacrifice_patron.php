<?php
/*
  ◆身代わり地蔵 (sacrifice_patron)
  ○仕様
  ・追加役職：庇護者
  ・人狼襲撃耐性：無効
*/
RoleLoader::LoadFile('patron');
class Role_sacrifice_patron extends Role_patron {
  protected function AddDuelistRole(User $user) {
    $this->AddPatronRole($user, 'protected');
  }

  public function WolfEatResist() {
    return true;
  }
}
