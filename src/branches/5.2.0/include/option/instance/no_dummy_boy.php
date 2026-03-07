<?php
/*
  ◆身代わり君なし
*/
class Option_no_dummy_boy extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    if (RoomOptionManager::IsChange()) {
      if (DB::$ROOM->IsOption('dummy_boy') || DB::$ROOM->IsOption('gm_login')) {
	$this->enable = false;
      } else {
	$this->enable = true;
      }
    }
  }

  public function GetCaption() {
    return '身代わり君なし';
  }
}
