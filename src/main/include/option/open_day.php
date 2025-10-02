<?php
/*
  ◆オープニングあり (open_day)
*/
class Option_open_day extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return 'オープニングあり'; }

  function GetExplain() { return 'ゲームが1日目「昼」からスタートします'; }
}
