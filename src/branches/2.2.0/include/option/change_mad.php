<?php
/*
  ◆狂人置換村 (change_mad)
  ○仕様
  ・配役：狂人 → CastConfig::$replace_role_list
*/
OptionManager::Load('replace_human');
class Option_change_mad extends Option_replace_human {
  function GetCaption() { return '狂人置換村'; }

  function GetExplain() { return '「狂人」が全員特定の役職に入れ替わります'; }
}
