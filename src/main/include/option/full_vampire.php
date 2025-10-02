<?php
/*
  ◆吸血鬼村 (full_vampire)
  ○仕様
  ・配役：村人 → 吸血鬼
*/
OptionManager::Load('replace_human');
class Option_full_vampire extends Option_replace_human {
  function GetCaption() { return '吸血鬼村'; }
}
