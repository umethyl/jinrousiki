<?php
/*
  ◆巫女登場 (medium)
  ○仕様
  ・配役：村人2 → 巫女1・女神1
*/
class Option_medium extends CheckRoomOptionItem {
  function GetCaption() { return '巫女登場'; }

  function GetExplain() { return '突然死した人の所属陣営が分かります [村人2→巫女1・女神1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && $list['human'] > 1) {
      $list['human'] -= 2;
      $list[$this->name]++;
      $list['mind_cupid']++;
    }
  }
}
