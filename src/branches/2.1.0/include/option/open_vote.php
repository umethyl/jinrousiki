<?php
/*
  ◆投票した票数を公表する (open_vote)
*/
class Option_open_vote extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '投票した票数を公表する'; }

  function GetExplain() { return '「権力者」などのサブ役職が分かりやすくなります'; }
}
