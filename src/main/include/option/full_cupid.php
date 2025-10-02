<?php
/*
  ◆キューピッド村 (full_cupid)
  ○仕様
  ・配役：村人 → キューピッド
*/
OptionManager::Load('replace_human');
class Option_full_cupid extends Option_replace_human {
  function GetCaption() { return 'キューピッド村'; }
}
