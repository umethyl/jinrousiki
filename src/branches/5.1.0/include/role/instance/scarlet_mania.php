<?php
/*
  ◆紅天女 (scarlet_mania)
  ○仕様
  ・コピー：メイン役職
*/
RoleLoader::LoadFile('mania');
class Role_scarlet_mania extends Role_mania {
  protected function GetCopiedRole() {
    return 'copied_nymph';
  }
}
