<?php
/*
  ◆共有者置換村 (change_common)
  ○仕様
  ・配役：村人 → CastConfig::$replace_role_list
*/
OptionManager::Load('replace_human');
class Option_change_common extends Option_replace_human {
  function GetCaption() { return '共有者置換村'; }
}
