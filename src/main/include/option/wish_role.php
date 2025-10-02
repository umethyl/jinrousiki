<?php
/*
  ◆役割希望制 (wish_role)
*/
class Option_wish_role extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption(){ return '役割希望制'; }

  function GetExplain(){ return '希望の役割を指定できますが、なれるかは運です'; }
}
