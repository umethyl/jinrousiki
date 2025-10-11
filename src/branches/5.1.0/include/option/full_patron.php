<?php
/*
  ◆後援者村 (full_patron)
  ○仕様
  ・配役：村人 → 後援者
*/
OptionLoader::LoadFile('replace_human');
class Option_full_patron extends Option_replace_human {
  public function GetCaption() {
    return '後援者村';
  }
}
