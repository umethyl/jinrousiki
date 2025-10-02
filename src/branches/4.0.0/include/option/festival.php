<?php
/*
  ◆お祭り村 (festival)
*/
class Option_festival extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'お祭り村';
  }

  public function GetExplain() {
    return '管理人がカスタムする特殊設定です';
  }
}
