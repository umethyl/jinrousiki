<?php
/*
  ◆急所通知 (notice_critical)
*/
class Option_notice_critical extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '急所通知';
  }

  public function GetExplain() {
    return '「会心」「痛恨」の発動が通知されます';
  }
}
