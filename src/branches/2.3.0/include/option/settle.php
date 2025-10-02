<?php
/*
  ◆決着村 (settle)
*/
class Option_settle extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '決着村';
  }

  public function GetExplain() {
    return '処刑投票による引き分けが発生しません';
  }
}
