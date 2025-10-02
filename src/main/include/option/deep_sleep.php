<?php
/*
  ◆静寂村 (deep_sleep)
  ○仕様
  ・配役：全員に爆睡者
*/
class Option_deep_sleep extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '静寂村'; }

  function GetExplain() { return '全員に「爆睡者」がつきます'; }

  function Cast(&$list, &$rand) { return $this->CastAll($list); }
}
