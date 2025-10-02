<?php
/*
  ◆子狐登場 (child_fox)
  ○仕様
  ・配役：妖狐 → 子狐
*/
class Option_child_fox extends CheckRoomOptionItem {
  function GetCaption() { return '子狐登場'; }

  function GetExplain() {
    return '限定的な占い能力を持ち、占い結果が「村人」・霊能結果が「子狐」となる妖狐です<br>' .
      '　　　[妖狐1→子狐1]';
  }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name}) {
      OptionManager::Replace($list, 'fox', $this->name);
    }
  }
}
