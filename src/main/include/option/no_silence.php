<?php
/*
  ◆沈黙禁止 (no_silence)
*/
class Option_no_silence extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '沈黙禁止';
  }

  public function GetExplain() {
    return '昼に一度も発言がない人を処刑投票処理時に突然死させます';
  }
}
