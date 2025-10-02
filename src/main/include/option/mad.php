<?php
/*
  ◆狂人追加 (mad)
  ○仕様
  ・配役：村人 → 妖狐
*/
class Option_mad extends CheckRoomOptionItem {
  public function GetCaption() {
    return '狂人追加';
  }

  public function GetExplain() {
    return '狂人をもう一人追加します [村人1→狂人1]';
  }

  public function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'human', $this->name);
    }
  }
}
