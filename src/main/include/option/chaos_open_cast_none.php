<?php
/*
  ◆配役通知 - 通知なし
*/
OptionLoader::LoadFile('chaos_open_cast_full');
class Option_chaos_open_cast_none extends Option_chaos_open_cast_full {
  public function GetName() {
    return $this->GetCaption();
  }

  public function GetCaption() {
    return '通知なし';
  }

  public function GetExplain() {
    return $this->GetCaption();
  }

  public function IgnoreCastMessage() {
    return true;
  }
}
