<?php
/*
  ◆キューピッド置換村 (セレクタ)
*/
OptionManager::Load('replace_human_selector');
class Option_change_cupid_selector extends Option_replace_human_selector {
  public $on_change = ' onChange="change_change_cupid();"';

  function GetCaption() { return 'キューピッド置換村'; }

  function GetExplain() { return '「キューピッド」が全員特定の役職に入れ替わります'; }
}
