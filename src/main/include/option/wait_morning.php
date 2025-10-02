<?php
/*
  ◆早朝待機制 (wait_morning)
*/
class Option_wait_morning extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '早朝待機制';
  }

  public function GetExplain() {
    return '夜が明けてから一定時間の間発言ができません';
  }
}
