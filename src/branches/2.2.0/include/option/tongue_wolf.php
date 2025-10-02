<?php
/*
  ◆舌禍狼登場 (tongue_wolf)
  ○仕様
  ・配役：人狼 → 舌禍狼
*/
class Option_tongue_wolf extends CheckRoomOptionItem {
  function GetCaption() { return '舌禍狼登場'; }

  function GetExplain() { return '襲撃した人の役職が分かる狼です [人狼1→舌禍狼1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'wolf', $this->name);
    }
  }
}
