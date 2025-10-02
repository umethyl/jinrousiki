<?php
/*
  ◆完全通知 (chaos_open_cast_full)
*/
OptionLoader::LoadFile('chaos_open_cast_none');
class Option_chaos_open_cast_full extends Option_chaos_open_cast_none {
  public function GetName() {
    return '完全通知';
  }

  public function GetCaption() {
    return '配役を通知する:完全通知';
  }

  public function GetExplain() {
    return '完全通知 (通常村相当)';
  }
}
