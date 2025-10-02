<?php
/*
  ◆小悪魔村 (change_triangle_cupid)
  ○仕様
  ・配役：キューピッド → 小悪魔
*/
OptionManager::Load('replace_human');
class Option_change_triangle_cupid extends Option_replace_human {
  function GetCaption() { return '小悪魔村'; }
}
