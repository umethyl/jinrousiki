<?php
/*
  ◆クイズ村 (quiz)
  ○仕様
  ・配役：解答者付加 (出題者以外)
*/
class Option_quiz extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  public function GetCaption() {
    return 'クイズ村';
  }

  public function GetExplain() {
    return 'GM が出題者になり、プレイヤー全員に回答者がつきます。';
  }

  protected function IgnoreCastAll($id) {
    $stack = Cast::Stack()->Get('fix_uname');
    return $stack[$id] == GM::DUMMY_BOY;
  }

  protected function GetCastAllRole($id) {
    return 'panelist';
  }

  protected function GetResultCastList() {
    return array('panelist');
  }
}
