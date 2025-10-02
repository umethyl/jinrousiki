<?php
/*
  ◆紳士・淑女村 (gentleman)
  ○仕様
  ・配役：性別に応じた紳士・淑女 / 全員
*/
class Option_gentleman extends CheckRoomOptionItem {
  function GetCaption() { return '紳士・淑女村'; }

  function GetExplain() { return '全員に性別に応じた「紳士」「淑女」がつきます'; }

  function Cast(array &$list, &$rand) {
    $stack = array('male' => 'gentleman', 'female' => 'lady');
    foreach (array_keys($list) as $id) {
      $list[$id] .= ' ' . $stack[DB::$USER->ByUname(RoleManager::$get->uname_list[$id])->sex];
    }
    return array('gentleman', 'lady');
  }
}
