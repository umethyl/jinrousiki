<?php
/*
  ◆お祭り村 (festival)
*/
class Option_festival extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return 'お祭り村';
  }

  public function GetExplain() {
    return '管理人がカスタムする特殊設定です';
  }
}
