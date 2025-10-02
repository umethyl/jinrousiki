<?php
/*
  ◆白狼登場 (boss_wolf)
  ○仕様
  ・配役：人狼 → 白狼
*/
class Option_boss_wolf extends CheckRoomOptionItem {
  function GetCaption() { return '白狼登場'; }

  function GetExplain() {
    return '占い結果が「村人」・霊能結果が「白狼」と表示される狼です [人狼1→白狼1]';
  }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'wolf', $this->name);
    }
  }
}
