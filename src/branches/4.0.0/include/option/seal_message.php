<?php
/*
  ◆天啓封印 (seal_message)
*/
class Option_seal_message extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '天啓封印';
  }

  public function GetExplain() {
    return '一部の個人通知メッセージが表示されなくなります';
  }
}
