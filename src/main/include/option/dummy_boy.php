<?php
/*
  ◆初日の夜は身代わり君 (dummy_boy)
*/
class Option_dummy_boy extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    if (true === $this->enable && OptionManager::IsChange()) {
      $this->enable = (false === DB::$ROOM->IsOption('gm_login'));
    }
  }

  public function LoadPost() {
    RoomOption::Set($this->group, $this->name);
  }

  public function GetCaption() {
    return '初日の夜は身代わり君';
  }

  public function GetExplain() {
    return '身代わり君あり (初日の夜、身代わり君が狼に食べられます)';
  }
}
