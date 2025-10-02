<?php
/*
  ◆狂人村 (full_mad)
  ○仕様
  ・配役：村人 → 狂人
*/
OptionManager::Load('replace_human');
class Option_full_mad extends Option_replace_human {
  function GetCaption() { return '狂人村'; }
}
