<?php
/*
  ◆殉教者村 (change_immolate_mad)
  ○仕様
  ・配役：狂人 → 殉教者
*/
OptionManager::Load('replace_human');
class Option_change_immolate_mad extends Option_replace_human {
  function GetCaption() { return '殉教者村'; }
}
