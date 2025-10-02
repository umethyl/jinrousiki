<?php
/*
  ◆背徳者登場 (depraver)
  ○仕様
  ・配役：村人 → 背徳者
*/
class Option_depraver extends CheckRoomOptionItem {
  public function GetCaption() {
    return '背徳者登場';
  }

  public function GetExplain() {
    return '妖狐陣営の狂人相当です。妖狐陣営が全滅すると後追い自殺します [村人1→背徳者1]';
  }

  public function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'human', $this->name);
    }
  }
}
