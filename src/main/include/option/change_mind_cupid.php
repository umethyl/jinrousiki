<?php
/*
  ◆女神村 (change_mind_cupid)
  ○仕様
  ・配役：キューピッド → 女神
*/
OptionManager::Load('replace_human');
class Option_change_mind_cupid extends Option_replace_human {
  function GetCaption() { return '女神村'; }
}
