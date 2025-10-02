<?php
/*
  ◆神話マニア村 (full_mania)
  ○仕様
  ・配役：村人 → 神話マニア
*/
OptionLoader::LoadFile('replace_human');
class Option_full_mania extends Option_replace_human {
  public function GetCaption() {
    return '神話マニア村';
  }
}
