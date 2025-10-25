<?php
/*
  ◆秘密会話あり (secret_talk)
*/
class Option_secret_talk extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return '秘密会話あり';
  }

  public function GetExplain() {
    return '秘密の発言が仲間同士で見えるようになります';
  }
}
