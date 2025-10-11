<?php
/*
  ◆舌禍狼登場 (tongue_wolf)
  ○仕様
  ・配役：人狼 → 舌禍狼
*/
OptionLoader::LoadFile('boss_wolf');
class Option_tongue_wolf extends Option_boss_wolf {
  public function GetCaption() {
    return '舌禍狼登場';
  }

  public function GetExplain() {
    return '襲撃した人の役職が分かる狼です [人狼1→舌禍狼1]';
  }
}
