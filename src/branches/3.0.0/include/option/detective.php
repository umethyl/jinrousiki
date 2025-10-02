<?php
/*
  ◆探偵村 (detective)
  ○仕様
  ・配役：探偵 (神話マニア ＞ 共有者 ＞ 村人)
*/
class Option_detective extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '探偵村';
  }

  public function GetExplain() {
    return '「探偵」が登場し、初日の夜に全員に公表されます';
  }

  public function SetRole(array &$list, $count) {
    foreach (array('mania', 'common', 'human') as $role) {
      if (OptionManager::Replace($list, $role, 'detective_common')) break;
    }
  }
}
