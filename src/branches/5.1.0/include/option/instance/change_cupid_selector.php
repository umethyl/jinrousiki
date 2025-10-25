<?php
/*
  ◆キューピッド置換村 (セレクタ)
*/
OptionLoader::LoadFile('replace_human_selector');
class Option_change_cupid_selector extends Option_replace_human_selector {
  public $on_change = ' onChange="change_change_cupid();"';

  public function GetCaption() {
    return 'キューピッド置換村';
  }

  public function GetExplain() {
    return '「キューピッド」が全員特定の役職に入れ替わります';
  }
}
