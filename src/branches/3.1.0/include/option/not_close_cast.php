<?php
/*
  ◆霊界常時公開
*/
class Option_not_close_cast extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  public function GetCaption() {
    return '常時公開 (蘇生能力は無効です)';
  }
}
