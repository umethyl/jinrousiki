<?php
/*
  ◆遺言制限 (limit_last_words)
*/
class Option_limit_last_words extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '遺言制限';
  }

  public function GetExplain() {
    return 'ゲーム開始以降は遺言の更新ができません';
  }
}
