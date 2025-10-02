<?php
/*
  ◆隠者 (hermit_common)
  ○仕様
  ・囁き：非表示
*/
RoleManager::LoadFile('common');
class Role_hermit_common extends Role_common {
  function Whisper(TalkBuilder $builder, $voice) { return false; }
}
