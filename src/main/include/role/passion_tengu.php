<?php
/*
  ◆尼天狗 (passion_tengu)
  ○仕様
  ・神通力：恋色迷彩
*/
RoleLoader::LoadFile('tengu');
class Role_passion_tengu extends Role_tengu {
  protected function TenguKill(User $user) {
    $user->AddRole('passion');
  }
}
