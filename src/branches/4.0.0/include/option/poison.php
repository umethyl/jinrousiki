<?php
/*
  ◆埋毒者登場 (poison)
  ○仕様
  ・配役：村人2 → 埋毒者1・人狼1
*/
class Option_poison extends OptionCheckbox {
  public function GetCaption() {
    return '埋毒者登場';
  }

  public function GetExplain() {
    return '処刑されたり狼に食べられた場合、道連れにします [村人2→埋毒1・人狼1]';
  }

  public function SetRole(array &$list, $count) {
    $role = 'human';
    if ($count >= CastConfig::${$this->name} && ArrayFilter::GetInt($list, $role) > 1) {
      OptionManager::Replace($list, $role, $this->name);
      OptionManager::Replace($list, $role, 'wolf');
    }
  }

  public function GetWishRole() {
    return [$this->name];
  }
}
