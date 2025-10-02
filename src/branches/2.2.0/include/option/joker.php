<?php
/*
  ◆ジョーカー村 (joker)
  ○仕様
  ・配役：誰か一人にジョーカー
*/
class Option_joker extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return 'ババ抜き村'; }

  function GetExplain() { return '誰か一人に「ジョーカー」がつきます'; }

  function Cast(array &$list, &$rand) { $this->CastOnce($list, $rand, '[2]'); }
}
