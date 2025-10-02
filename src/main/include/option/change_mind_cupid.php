<?php
/*
  ◆女神村 (change_mind_cupid)
  ○仕様
  ・配役：キューピッド → 女神
*/
OptionLoader::LoadFile('change_cupid');
class Option_change_mind_cupid extends Option_change_cupid {
  public function GetCaption() {
    return '女神村';
  }
}
