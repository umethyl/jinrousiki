<?php
/*
  ◆恋色迷彩村 (passion)
  ○仕様
  ・配役：全員に「恋色迷彩」
*/
class Option_passion extends CheckRoomOptionItem {
  public function GetCaption() {
    return '恋色迷彩村';
  }

  public function GetExplain() {
    return '全員に「恋色迷彩」がつきます';
  }

  public function Cast(array &$list, &$rand) {
    return $this->CastAll($list);
  }
}
