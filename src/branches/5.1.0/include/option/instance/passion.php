<?php
/*
  ◆恋色迷彩村 (passion)
  ○仕様
  ・配役：全員に恋色迷彩
*/
class Option_passion extends OptionCheckbox {
  public function GetCaption() {
    return '恋色迷彩村';
  }

  public function GetExplain() {
    return '全員に「恋色迷彩」がつきます';
  }
}
