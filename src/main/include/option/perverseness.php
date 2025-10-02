<?php
/*
  ◆天邪鬼村 (perverseness)
  ○仕様
  ・配役：全員に天邪鬼
*/
class Option_perverseness extends OptionCheckbox {
  public function GetCaption() {
    return '天邪鬼村';
  }

  public function GetExplain() {
    return '全員に「天邪鬼」がつきます。一部のサブ役職系オプションが強制オフになります';
  }
}
