<?php
/*
  ◆天候あり (weather)
*/
class Option_weather extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '天候あり';
  }

  public function GetExplain() {
    return '「天候」と呼ばれる特殊イベントが発生します';
  }
}
