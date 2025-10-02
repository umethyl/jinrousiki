<?php
/*
  ◆トリップ必須 (necessary_trip)
*/
class Option_necessary_trip extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function __construct() {
    if (GameConfig::TRIP) parent::__construct();
  }

  protected function IgnorePost() {
    return ! GameConfig::TRIP;
  }

  public function GetCaption() {
    return 'トリップ必須';
  }

  public function GetExplain() {
    return 'ユーザ登録名にトリップが必須です';
  }
}
