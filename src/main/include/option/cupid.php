<?php
/*
  ◆キューピッド登場 (cupid)
  ○仕様
  ・配役：村人 → キューピッド
*/
class Option_cupid extends OptionCheckbox {
  public function GetCaption() {
    return 'キューピッド登場';
  }

  public function GetExplain() {
    return '初日夜に選んだ相手を恋人にします。恋人となった二人は勝利条件が変化します' .
      Text::BR . '　　　[村人1→キューピッド1]';
  }

  public function FilterCastAddRole(array &$list, $count) {
    $option = 'full_' . $this->name;
    if ($count >= CastConfig::${$this->name} && false === DB::$ROOM->IsOption($option)) {
      OptionManager::CastRoleReplace($list, 'human', $this->name);
      OptionManager::StoreDummyBoyCastLimit([$this->name]);
    }
  }

  public function GetWishRole() {
    return [$this->name];
  }
}
