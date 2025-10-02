<?php
/*
  ◆人狼追加 (wolf)
  ○仕様
  ・配役：村人 → 人狼
*/
class Option_wolf extends CheckRoomOptionItem {
  public function GetCaption() {
    return '人狼追加';
  }

  public function GetExplain() {
    return '人狼をもう一人追加します [村人1→人狼1]';
  }

  public function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'human', $this->name);
    }
  }
}
