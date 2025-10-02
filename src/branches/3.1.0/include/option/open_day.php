<?php
/*
  ◆オープニングあり (open_day)
*/
class Option_open_day extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'オープニングあり';
  }

  public function GetExplain() {
    return 'ゲームが1日目「昼」からスタートします';
  }
}
