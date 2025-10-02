<?php
/*
  ◆暗殺者登場 (assassin)
  ○仕様
  ・配役：村人2 → 暗殺者1・人狼1
*/
class Option_assassin extends CheckRoomOptionItem {
  function GetCaption() { return '暗殺者登場'; }

  function GetExplain() {
    return '夜に村人一人を暗殺することができます [村人2→暗殺者1・人狼1]';
  }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && $list['human'] > 1) {
      $list['human'] -= 2;
      $list[$this->name]++;
      $list['wolf']++;
    }
  }
}
