<?php
/*
  ◆蝙蝠村 (full_chiroptera)
  ○仕様
  ・配役：村人 → 蝙蝠
*/
OptionLoader::LoadFile('replace_human');
class Option_full_chiroptera extends Option_replace_human {
  public function GetCaption() {
    return '蝙蝠村';
  }
}
