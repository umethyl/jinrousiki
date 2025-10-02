<?php
/*
  ◆鵺村 (full_unknown_mania)
  ○仕様
  ・配役：村人 → 鵺
*/
OptionManager::Load('replace_human');
class Option_full_unknown_mania extends Option_replace_human {
  function GetCaption() { return '鵺村'; }
}
