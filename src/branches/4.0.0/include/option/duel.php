<?php
/*
  ◆決闘村 (duel)
  ○仕様
*/
class Option_duel extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '決闘村';
  }
}
