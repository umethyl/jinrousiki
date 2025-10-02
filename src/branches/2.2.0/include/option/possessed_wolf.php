<?php
/*
  ◆憑狼登場 (possessed_wolf)
  ○仕様
  ・配役：人狼 → 憑狼
*/
class Option_possessed_wolf extends CheckRoomOptionItem {
  function GetCaption() { return '憑狼登場'; }

  function GetExplain() { return '襲撃した人に憑依して乗っ取ってしまう狼です [人狼1→憑狼1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'wolf', $this->name);
    }
  }
}
