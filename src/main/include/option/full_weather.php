<?php
/*
  ◆天変地異 (full_weather)
*/
class Option_full_weather extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '天変地異';
  }

  public function GetExplain() {
    return '「天候」が毎日発生します';
  }
}
