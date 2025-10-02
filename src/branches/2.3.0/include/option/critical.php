<?php
/*
  ◆急所村 (critical)
  ○仕様
  ・配役：全員に会心・痛恨
*/
class Option_critical extends CheckRoomOptionItem {
  public function GetCaption() {
    return '急所村';
  }

  public function GetExplain() {
    return '全員に「会心」「痛恨」がつきます。';
  }

  public function Cast(array &$list, &$rand) {
    foreach (array_keys($list) as $id) {
      $list[$id] .= ' critical_voter critical_luck';
    }
    return array('critical_voter', 'critical_luck');
  }
}
