<?php
/*
  ◆トリップ必須 (necessary_trip)
*/
class Option_necessary_trip extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  protected function Ignore() {
    return false === GameConfig::TRIP;
  }

  protected function IgnorePost() {
    return $this->Ignore();
  }

  public function GetCaption() {
    return 'トリップ必須';
  }

  public function GetExplain() {
    return 'ユーザ登録名にトリップが必須です';
  }
}
