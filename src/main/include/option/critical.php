<?php
/*
  ◆急所村 (critical)
  ○仕様
*/
class Option_critical extends CheckRoomOptionItem {
  function GetCaption() { return '急所村'; }

  function GetExplain() { return '全員に「会心」「痛恨」がつきます。'; }

  function Cast(array &$list, &$rand) {
    foreach (array_keys($list) as $id) $list[$id] .= ' critical_voter critical_luck';
    return array('critical_voter', 'critical_luck');
  }
}
