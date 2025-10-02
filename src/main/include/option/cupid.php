<?php
/*
  ◆キューピッド登場 (cupid)
  ○仕様
  ・配役：村人 → キューピッド
*/
class Option_cupid extends CheckRoomOptionItem {
  function GetCaption() { return 'キューピッド登場'; }

  function GetExplain() {
    return '初日夜に選んだ相手を恋人にします。恋人となった二人は勝利条件が変化します<br>' .
      '　　　[村人1→キューピッド1]';
  }

  function SetRole(array &$list, $count) {
    if ($count >= CastConfig::${$this->name} && ! DB::$ROOM->IsOption('full_' . $this->name)) {
      OptionManager::Replace($list, 'human', $this->name);
    }
  }
}
