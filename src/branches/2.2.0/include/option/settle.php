<?php
/*
  ◆決着村 (settle)
*/
class Option_settle extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '決着村'; }

  function GetExplain() { return '処刑投票による引き分けが発生しません'; }
}
