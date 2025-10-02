<?php
/*
  ◆紳士・淑女村 (gentleman)
  ○仕様
  ・配役：性別に応じた紳士・淑女 / 全員
*/
class Option_gentleman extends OptionCheckbox {
  public function GetCaption() {
    return '紳士・淑女村';
  }

  public function GetExplain() {
    return '全員に性別に応じた「紳士」「淑女」がつきます';
  }

  protected function GetCastUserSubRoleAllRole($id) {
    $stack = Cast::Stack()->Get(Cast::UNAME);
    return Sex::IsMale(DB::$USER->ByUname($stack[$id])) ? 'gentleman' : 'lady';
  }

  protected function GetResultCastUserSubRoleList() {
    return ['gentleman', 'lady'];
  }
}
