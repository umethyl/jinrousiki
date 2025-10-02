<?php
/*
  ◆デスノート村 (death_note)
*/
class Option_death_note extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return 'デスノート村';
  }

  public function GetExplain() {
    return '毎日、誰か一人に「デスノート」が与えられます';
  }
}
