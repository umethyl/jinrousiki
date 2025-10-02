<?php
/*
  ◆天使村 (change_angel)
  ○仕様
  ・配役：キューピッド → 天使
*/
OptionManager::Load('change_cupid');
class Option_change_angel extends Option_change_cupid {
  public function GetCaption() {
    return '天使村';
  }
}
