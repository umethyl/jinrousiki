<?php
/*
  ◆狂信者村 (change_fanatic_mad)
  ○仕様
  ・配役：狂人 → 囁き狂人
*/
OptionManager::Load('replace_human');
class Option_change_fanatic_mad extends Option_replace_human {
  function GetCaption() { return '狂信者村'; }
}
