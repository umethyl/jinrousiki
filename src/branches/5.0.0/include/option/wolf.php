<?php
/*
  ◆人狼追加 (wolf)
  ○仕様
  ・配役：村人 → 人狼
*/
class Option_wolf extends OptionCheckbox {
  public function GetCaption() {
    return '人狼追加';
  }

  public function GetExplain() {
    return '人狼をもう一人追加します [村人1→人狼1]';
  }

  public function FilterCastAddRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::CastRoleReplace($list, 'human', $this->name);
      OptionManager::StoreDummyBoyCastLimit([$this->name]);
    }
  }

  public function GetWishRole() {
    return [$this->name];
  }
}
