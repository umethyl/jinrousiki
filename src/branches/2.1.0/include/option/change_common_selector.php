<?php
/*
  ◆共有者置換村 (セレクタ)
*/
OptionManager::Load('replace_human_selector');
class Option_change_common_selector extends Option_replace_human_selector {
  function GetCaption() { return '共有者置換村'; }

  function GetExplain() { return '「共有者」が全員特定の役職に入れ替わります'; }
}
