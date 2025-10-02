<?php
/*
  ◆囁き狂人村 (change_whisper_mad)
  ○仕様
  ・配役：狂人 → 囁き狂人
*/
OptionManager::Load('replace_human');
class Option_change_whisper_mad extends Option_replace_human {
  function GetCaption() { return '囁き狂人村'; }
}
