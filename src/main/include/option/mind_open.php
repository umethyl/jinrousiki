<?php
/*
  ◆白夜村 (mind_open)
  ○仕様
  ・配役：全員に公開者
*/
class Option_mind_open extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return '白夜村'; }

  function GetExplain() { return '全員に「公開者」がつきます'; }

  function Cast(array &$list, &$rand) { return $this->CastAll($list); }
}
