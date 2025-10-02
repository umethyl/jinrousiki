<?php
/*
  ◆恋色迷彩村 (passion)
  ○仕様
  ・配役：全員に「恋色迷彩」
*/
class Option_passion extends CheckRoomOptionItem {
  function GetCaption() { return '恋色迷彩村'; }

  function GetExplain() { return '全員に「恋色迷彩」がつきます'; }

  function Cast(&$list, &$rand) { return $this->CastAll($list); }
}
