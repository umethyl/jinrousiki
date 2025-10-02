<?php
/*
  ◆白夜村 (mind_open)
  ○仕様
  ・配役：全員に公開者
*/
class Option_mind_open extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return '白夜村';
  }

  public function GetExplain() {
    return '全員に「公開者」がつきます';
  }

  public function Cast(array &$list, &$rand) {
    return $this->CastAll($list);
  }
}
