<?php
/*
  ◆囁き狂人村 (change_whisper_mad)
  ○仕様
  ・配役：狂人 → 囁き狂人
*/
OptionManager::Load('change_mad');
class Option_change_whisper_mad extends Option_change_mad {
  function GetCaption() { return '囁き狂人村'; }
}
