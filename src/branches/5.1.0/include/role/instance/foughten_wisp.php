<?php
/*
  ◆古戦場火 (foughten_wisp)
  ○仕様
  ・占い結果：蝙蝠
*/
RoleLoader::LoadFile('wisp');
class Role_foughten_wisp extends Role_wisp {
  protected function GetWispRole($reverse) {
    return 'chiroptera';
  }
}
