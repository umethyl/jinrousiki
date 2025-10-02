<?php
/*
  ◆探偵村 (detective)
*/
class Option_detective extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '探偵村'; }

  function GetExplain() { return '「探偵」が登場し、初日の夜に全員に公表されます'; }

  function SetRole(array &$list, $count) {
    foreach (array('mania', 'common', 'human') as $role) {
      if (OptionManager::Replace($list, $role, 'detective_common')) break;
    }
  }
}
