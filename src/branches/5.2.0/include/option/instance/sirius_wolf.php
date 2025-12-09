<?php
/*
  ◆天狼登場 (sirius_wolf)
  ○仕様
  ・配役：人狼 → 天狼
*/
OptionLoader::LoadFile('boss_wolf');
class Option_sirius_wolf extends Option_boss_wolf {
  public function GetCaption() {
    return '天狼登場';
  }

  public function GetExplain() {
    return '仲間が減ると特殊能力が発現する狼です [人狼1→天狼1]';
  }
}
