<?php
/*
  ◆殉教者村 (change_immolate_mad)
  ○仕様
  ・配役：狂人 → 殉教者
*/
OptionManager::Load('change_mad');
class Option_change_immolate_mad extends Option_change_mad {
  function GetCaption() { return '殉教者村'; }
}
