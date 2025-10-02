<?php
/*
  ◆闇鍋モード (chaos)
*/
class Option_chaos extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '闇鍋モード'; }

  protected function GetURL() { return 'chaos.php#' . $this->name; }
}
