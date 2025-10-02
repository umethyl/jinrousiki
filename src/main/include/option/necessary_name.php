<?php
/*
  ◆ユーザ名必須 (necessary_name)
*/
class Option_necessary_name extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function __construct() {
    if (GameConfig::TRIP) parent::__construct();
  }

  function GetCaption() { return 'ユーザ名必須'; }

  function GetExplain() { return 'トリップのみのユーザ名登録はできません'; }
}
