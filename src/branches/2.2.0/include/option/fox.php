<?php
/*
  ◆妖狐追加 (fox)
  ○仕様
  ・配役：村人 → 妖狐
*/
class Option_fox extends CheckRoomOptionItem {
  function GetCaption() { return '妖狐追加'; }

  function GetExplain() { return '妖狐をもう一人追加します [村人1→妖狐1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'human', $this->name);
    }
  }
}
