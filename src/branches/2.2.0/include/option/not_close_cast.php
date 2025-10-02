<?php
/*
  ◆霊界常時公開
*/
class Option_not_close_cast extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'radio';

  function GetCaption() { return '常時公開 (蘇生能力は無効です)'; }
}
