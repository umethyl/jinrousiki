<?php
/*
  ◆身代わり君は GM (gm_login)
*/
class Option_gm_login extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'radio';

  function GetCaption() { return '身代わり君は GM'; }

  function GetExplain() { return '仮想 GM が身代わり君としてログインします'; }
}
