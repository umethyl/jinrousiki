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
    $role = 'human';
    if ($count >= CastConfig::${$this->name} && isset($list[$role]) && $list[$role] > 1) {
      OptionManager::Replace($list, $role, $this->name);
      OptionManager::Replace($list, $role, 'mind_cupid');
    }
  }
}
