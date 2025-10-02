<?php
/*
  ◆宵闇村 (blinder)
  ○仕様
  ・配役：全員に目隠し
*/
class Option_blinder extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '宵闇村';
  }

  public function GetExplain() {
    return '全員に「目隠し」がつきます';
  }
}
