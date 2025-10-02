<?php
/*
  ◆毒狼登場 (poison_wolf)
  ○仕様
  ・配役：人狼 → 毒狼 / 村人 → 薬師
*/
class Option_poison_wolf extends OptionCheckbox {
  public function GetCaption() {
    return '毒狼登場';
  }

  public function GetExplain() {
    return '処刑時にランダムで村人一人を巻き添えにする狼です' . Text::BR .
      '　　　[人狼1→毒狼1 / 村人1→薬師1]';
  }

  public function FilterCastAddRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} &&
	ArrayFilter::GetInt($list, 'wolf') > 0 && ArrayFilter::GetInt($list, 'human') > 0) {
      OptionManager::CastRoleReplace($list, 'wolf', $this->name);
      OptionManager::CastRoleReplace($list, 'human', 'pharmacist');
      OptionManager::StoreDummyBoyCastLimit(['pharmacist']);
    }
  }

  public function GetWishRole() {
    return [$this->name, 'pharmacist'];
  }
}
