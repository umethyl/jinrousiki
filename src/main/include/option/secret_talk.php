<?php
/*
  ◆早朝待機制 (secret_talk)
*/
class Option_secret_talk extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '秘密会話あり';
  }

  public function GetExplain() {
    return '秘密の発言が仲間同士で見えるようになります';
  }
}
