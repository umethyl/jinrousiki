<?php
/*
  ◆天邪鬼村 (perverseness)
*/
class Option_perverseness extends CheckRoomOptionItem {
  function GetCaption() { return '天邪鬼村'; }

  function GetExplain() {
    return '全員に「天邪鬼」がつきます。一部のサブ役職系オプションが強制オフになります';
  }

  function Cast(array &$list, &$rand) { return $this->CastAll($list); }
}
