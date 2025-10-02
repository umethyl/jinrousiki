<?php
/*
  ◆クイズ村 (quiz)
  ○仕様
  ・配役：解答者付加 (出題者以外)
*/
class Option_quiz extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;

  function GetCaption() { return 'クイズ村'; }

  function GetExplain() { return 'GM が出題者になり、プレイヤー全員に回答者がつきます。'; }

  function Cast(array &$list, &$rand) {
    $role  = 'panelist';
    $stack = RoleManager::GetStack('uname_list');
    foreach (array_keys($list) as $id) {
      if ($stack[$id] != 'dummy_boy') $list[$id] .= ' ' . $role;
    }
    return array($role);
  }
}
