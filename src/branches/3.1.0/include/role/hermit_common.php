<?php
/*
  ◆隠者 (hermit_common)
  ○仕様
  ・囁き：非表示
*/
RoleLoader::LoadFile('common');
class Role_hermit_common extends Role_common {
  public function Whisper(TalkBuilder $builder, TalkParser $talk) {
    return false;
  }
}
