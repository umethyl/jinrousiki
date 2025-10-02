<?php
/*
  ◆天火 (black_wisp)
  ○仕様
  ・占い結果：人狼
*/
RoleLoader::LoadFile('wisp');
class Role_black_wisp extends Role_wisp {
  protected function GetWispRole($reverse) {
    return (true === $reverse) ? 'human' : 'wolf';
  }
}
