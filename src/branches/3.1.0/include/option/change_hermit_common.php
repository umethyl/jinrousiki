<?php
/*
  ◆隠者村 (change_hermit_common)
  ○仕様
  ・配役：共有者 → 隠者
*/
OptionLoader::LoadFile('change_common');
class Option_change_hermit_common extends Option_change_common {
  public function GetCaption() {
    return '隠者村';
  }
}
