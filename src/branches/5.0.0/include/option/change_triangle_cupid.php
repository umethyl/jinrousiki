<?php
/*
  ◆小悪魔村 (change_triangle_cupid)
  ○仕様
  ・配役：キューピッド → 小悪魔
*/
OptionLoader::LoadFile('change_cupid');
class Option_change_triangle_cupid extends Option_change_cupid {
  public function GetCaption() {
    return '小悪魔村';
  }
}
