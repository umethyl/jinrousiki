<?php
/*
  ◆身代わり君なし
*/
class Option_no_dummy_boy extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    if (RoomOptionManager::IsChange()) {
      $this->enable = false;
    }
  }

  public function GetCaption() {
    return '身代わり君なし';
  }
}
