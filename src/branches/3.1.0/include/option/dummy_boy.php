<?php
/*
  ◆初日の夜は身代わり君 (dummy_boy)
*/
class Option_dummy_boy extends OptionCheckbox {
  public $group = OptionGroup::GAME;
  public $type  = OptionFormType::RADIO;

  protected function FilterEnable() {
    $enable = GameOptionConfig::$dummy_boy_enable;
    if (OptionManager::IsChange()) {
      $this->enable = $enable && ! DB::$ROOM->IsOption('gm_login');
    } else {
      $this->enable = $enable;
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
