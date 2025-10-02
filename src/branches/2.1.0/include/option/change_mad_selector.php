<?php
/*
  ◆狂人置換村 (セレクタ)
*/
OptionManager::Load('replace_human_selector');
class Option_change_mad_selector extends Option_replace_human_selector {
  function GetCaption() { return '狂人置換村'; }

  function GetExplain() { return '「狂人」が全員特定の役職に入れ替わります'; }
}
