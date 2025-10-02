<?php
/*
  ◆天変地異 (full_weather)
*/
class Option_full_weather extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '天変地異';
  }

  public function GetExplain() {
    return '「天候」が毎日発生します';
  }
}
