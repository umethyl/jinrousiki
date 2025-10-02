<?php
/*
  ◆神話マニア村 (full_mania)
  ○仕様
  ・配役：村人 → 神話マニア
*/
OptionManager::Load('replace_human');
class Option_full_mania extends Option_replace_human {
  function GetCaption() { return '神話マニア村'; }
}
