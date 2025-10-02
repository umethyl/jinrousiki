<?php
/*
  ◆共有者置換村 (change_common)
  ○仕様
  ・配役：共有者 → CastConfig::$replace_role_list
*/
OptionManager::Load('replace_human');
class Option_change_common extends Option_replace_human {
  function GetCaption() { return '共有者置換村'; }

  function GetExplain() { return '「共有者」が全員特定の役職に入れ替わります'; }
}
