<?php
/*
  ◆ゲルト君モード (gerd)
*/
class Option_gerd extends OptionCheckbox {
  public function GetCaption() {
    return 'ゲルト君モード';
  }

  public function GetExplain() {
    return '役職が村人固定になります [村人が出現している場合のみ有効]';
  }

  //闇鍋固定枠追加
  public function FilterChaosFixRole(array &$list) {
    ArrayFilter::Initialize($list, 'human', 1);
  }
}
