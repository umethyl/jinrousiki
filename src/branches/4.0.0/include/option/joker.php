<?php
/*
  ◆ジョーカー村 (joker)
  ○仕様
  ・配役：誰か一人にジョーカー
*/
class Option_joker extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'ババ抜き村';
  }

  public function GetExplain() {
    return '誰か一人に「ジョーカー」がつきます';
  }

  public function Cast() {
    return $this->CastOnce('[2]');
  }

  protected function GetResultCastList() {
    return null;
  }
}
