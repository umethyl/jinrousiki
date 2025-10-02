<?php
/*
  ◆闇鍋モード (chaos)
*/
class Option_chaos extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '闇鍋モード';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }
}
