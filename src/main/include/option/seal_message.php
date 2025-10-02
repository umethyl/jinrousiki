<?php
/*
  ◆天啓封印 (seal_message)
*/
class Option_seal_message extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '天啓封印'; }

  function GetExplain() { return '一部の個人通知メッセージが表示されなくなります'; }
}
