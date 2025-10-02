<?php
/*
  ◆決闘村 (duel)
  ○仕様
*/
class Option_duel extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '決闘村';
  }
}
