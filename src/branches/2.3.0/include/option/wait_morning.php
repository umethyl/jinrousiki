<?php
/*
  ◆早朝待機制 (wait_morning)
*/
class Option_wait_morning extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '早朝待機制';
  }

  public function GetExplain() {
    return '夜が明けてから一定時間の間発言ができません';
  }
}
