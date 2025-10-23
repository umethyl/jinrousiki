<?php
/*
  ◆腰痛村 (critical_chicken)
  ○仕様
  ・配役：全員に魔女の一撃
*/
class Option_critical_chicken extends OptionCheckbox {
  public function GetCaption() {
    return '腰痛村';
  }

  public function GetExplain() {
    return '全員に「魔女の一撃」がつきます';
  }
}
