<?php
/*
  ◆天使村 (change_angel)
  ○仕様
  ・配役：キューピッド → 天使
*/
OptionManager::Load('replace_human');
class Option_change_angel extends Option_replace_human {
  function GetCaption() { return '天使村'; }
}
