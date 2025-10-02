<?php
/*
  ◆神話マニア登場 (mania)
  ○仕様
  ・配役：村人 → 神話マニア
*/
OptionLoader::LoadFile('cupid');
class Option_mania extends Option_cupid {
  public function GetCaption() {
    return '神話マニア登場';
  }

  public function GetExplain() {
    return '初日夜に他の村人の役職をコピーします [村人1→神話マニア1]';
  }
}
