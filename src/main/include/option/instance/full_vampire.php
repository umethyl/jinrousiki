<?php
/*
  ◆吸血鬼村 (full_vampire)
  ○仕様
  ・配役：村人 → 吸血鬼
*/
OptionLoader::LoadFile('replace_human');
class Option_full_vampire extends Option_replace_human {
  public function GetCaption() {
    return '吸血鬼村';
  }
}
