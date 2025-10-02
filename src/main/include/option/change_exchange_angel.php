<?php
/*
  ◆魂移使村 (change_exchange_angel)
  ○仕様
  ・配役：キューピッド → 魂移使
*/
OptionManager::Load('change_cupid');
class Option_change_exchange_angel extends Option_change_cupid {
  public function GetCaption() {
    return '魂移使村';
  }
}
