<?php
/*
  ◆天狼登場 (sirius_wolf)
  ○仕様
  ・配役：人狼 → 天狼
*/
class Option_sirius_wolf extends CheckRoomOptionItem {
  function GetCaption() { return '天狼登場'; }

  function GetExplain() { return '仲間が減ると特殊能力が発現する狼です [人狼1→天狼1]'; }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && $list['wolf'] > 0) {
      $list['wolf']--;
      $list[$this->name]++;
    }
  }
}
