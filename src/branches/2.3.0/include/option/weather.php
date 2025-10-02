<?php
/*
  ◆天候あり (weather)
*/
class Option_weather extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '天候あり';
  }

  public function GetExplain() {
    return '「天候」と呼ばれる特殊イベントが発生します';
  }
}
