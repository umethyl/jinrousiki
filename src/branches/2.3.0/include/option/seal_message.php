<?php
/*
  ◆天啓封印 (seal_message)
*/
class Option_seal_message extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '天啓封印';
  }

  public function GetExplain() {
    return '一部の個人通知メッセージが表示されなくなります';
  }
}
