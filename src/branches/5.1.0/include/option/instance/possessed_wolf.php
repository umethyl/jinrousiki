<?php
/*
  ◆憑狼登場 (possessed_wolf)
  ○仕様
  ・配役：人狼 → 憑狼
*/
OptionLoader::LoadFile('boss_wolf');
class Option_possessed_wolf extends Option_boss_wolf {
  public function GetCaption() {
    return '憑狼登場';
  }

  public function GetExplain() {
    return '襲撃した人に憑依して乗っ取ってしまう狼です [人狼1→憑狼1]';
  }
}
