<?php
/*
  ◆村人置換村 (replace_human)
  ○仕様
  ・配役：村人 → CastConfig::$replace_role_list
*/
class Option_replace_human extends CheckRoomOptionItem {
  function GetCaption() { return '村人置換村'; }

  function GetExplain() { return '「村人」が全員特定の役職に入れ替わります'; }
}
