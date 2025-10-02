<?php
/*
  ◆トリップ必須 (necessary_trip)
*/
class Option_necessary_trip extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function __construct() {
    if (GameConfig::TRIP) parent::__construct();
  }

  function GetCaption() { return 'トリップ必須'; }

  function GetExplain() { return 'ユーザ登録名にトリップが必須です'; }
}
