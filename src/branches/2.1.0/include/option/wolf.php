<?php
/*
  ◆人狼追加 (wolf)
  ○仕様
  ・配役：村人 → 人狼
*/
class Option_wolf extends CheckRoomOptionItem {
  function GetCaption() { return '人狼追加'; }

  function GetExplain() { return '人狼をもう一人追加します [村人1→人狼1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && $list['human'] > 0) {
      $list['human']--;
      $list[$this->name]++;
    }
  }
}
