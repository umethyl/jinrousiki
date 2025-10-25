<?php
/*
  ◆村人振替村 (shuffle_human)
  ○仕様
  ・配役：村人 → 村人表記役職ランダム
*/
OptionLoader::LoadFile('replace_human');
class Option_shuffle_human extends Option_replace_human {
  public function GetCaption() {
    return '村人振替村';
  }
}
