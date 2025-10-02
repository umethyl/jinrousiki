<?php
/*
  ◆出題者村 (full_quiz)
  ○仕様
  ・配役：村人 → 出題者
*/
OptionManager::Load('replace_human');
class Option_full_quiz extends Option_replace_human {
  function GetCaption() { return '出題者村'; }
}
