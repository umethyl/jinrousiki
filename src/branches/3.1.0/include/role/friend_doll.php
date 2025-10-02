<?php
/*
  ◆仏蘭西人形 (friend_doll)
  ○仕様
  ・仲間表示：人形追加
*/
RoleLoader::LoadFile('doll');
class Role_friend_doll extends Role_doll {
  protected function IsDisplayDoll() {
    return true;
  }
}
