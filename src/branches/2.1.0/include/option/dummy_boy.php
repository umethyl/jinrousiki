<?php
/*
  ◆初日の夜は身代わり君 (dummy_boy)
*/
class Option_dummy_boy extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'radio';

  function GetCaption() { return '初日の夜は身代わり君'; }

  function GetExplain() { return '身代わり君あり (初日の夜、身代わり君が狼に食べられます)'; }
}
