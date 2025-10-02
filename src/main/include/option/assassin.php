<?php
/*
  ◆暗殺者登場 (assassin)
  ○仕様
  ・配役：村人2 → 暗殺者1・人狼1
*/
OptionLoader::LoadFile('poison');
class Option_assassin extends Option_poison {
  public function GetCaption() {
    return '暗殺者登場';
  }

  public function GetExplain() {
    return '夜に村人一人を暗殺することができます [村人2→暗殺者1・人狼1]';
  }
}
