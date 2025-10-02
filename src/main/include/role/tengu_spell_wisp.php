<?php
/*
  ◆天狗火 (tengu_spell_wisp)
  ○仕様
  ・占い結果：人狼 + 呪殺
*/
RoleLoader::LoadFile('wisp');
class Role_tengu_spell_wisp extends Role_wisp {
  protected function GetWispRole($reverse) {
    return (true === $reverse) ? 'human' : 'wolf';
  }
}
