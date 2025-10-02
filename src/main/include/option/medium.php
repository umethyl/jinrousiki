<?php
/*
  ◆巫女登場 (medium)
  ○仕様
  ・配役：村人2 → 巫女1・女神1
*/
class Option_medium extends OptionCheckbox {
  public function GetCaption() {
    return '巫女登場';
  }

  public function GetExplain() {
    return '突然死した人の所属陣営が分かります [村人2→巫女1・女神1]';
  }

  public function SetRole(array &$list, $count) {
    $role = 'human';
    if ($count >= CastConfig::${$this->name} && ArrayFilter::GetInt($list, $role) > 1) {
      OptionManager::Replace($list, $role, $this->name);
      OptionManager::Replace($list, $role, 'mind_cupid');
      OptionManager::StoreDummyBoyCastLimit([$this->name, 'mind_cupid']);
    }
  }

  public function GetWishRole() {
    return [$this->name, 'mind_cupid'];
  }
}
