<?php
/*
  ◆キューピッド置換村 (change_cupid)
  ○仕様
  ・配役：キューピッド → CastConfig::$replace_role_list
*/
OptionManager::Load('replace_human');
class Option_change_cupid extends Option_replace_human {
  function GetCaption() { return 'キューピッド置換村'; }

  function GetExplain() { return '「キューピッド」が全員特定の役職に入れ替わります'; }
}
