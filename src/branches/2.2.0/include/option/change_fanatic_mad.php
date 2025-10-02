<?php
/*
  ◆狂信者村 (change_fanatic_mad)
  ○仕様
  ・配役：狂人 → 囁き狂人
*/
OptionManager::Load('change_mad');
class Option_change_fanatic_mad extends Option_change_mad {
  function GetCaption() { return '狂信者村'; }
}
