<?php
/*
  ◆静寂村 (deep_sleep)
  ○仕様
  ・配役：全員に爆睡者
*/
class Option_deep_sleep extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '静寂村';
  }

  public function GetExplain() {
    return '全員に「爆睡者」がつきます';
  }
}
