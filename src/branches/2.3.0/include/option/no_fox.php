<?php
/*
  ◆妖狐無し (no_fox)
  ○仕様
  ・配役：妖狐 → 村人
*/
class Option_no_fox extends CheckRoomOptionItem {
  public function GetCaption() {
    return '妖狐なし';
  }

  public function GetExplain() {
    return '妖狐が出現しません [妖狐1→村人1]';
  }

  public function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'fox', 'human');
    }
  }
}
