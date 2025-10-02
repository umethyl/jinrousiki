<?php
/*
  ◆ユーザ名必須 (necessary_name)
*/
class Option_necessary_name extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  protected function Ignore() {
    return ! GameConfig::TRIP;
  }

  protected function IgnorePost() {
    return $this->Ignore();
  }

  public function GetCaption() {
    return 'ユーザ名必須';
  }

  public function GetExplain() {
    return 'トリップのみのユーザ名登録はできません';
  }
}
