<?php
/*
  ◆鵺村 (full_unknown_mania)
  ○仕様
  ・配役：村人 → 鵺
*/
OptionLoader::LoadFile('replace_human');
class Option_full_unknown_mania extends Option_replace_human {
  public function GetCaption() {
    return '鵺村';
  }
}
