<?php
/*
  ◆決定者登場 (decide)
  ○仕様
  ・配役：誰か一人に決定者
*/
class Option_decide extends CheckRoomOptionItem {
  function GetCaption() { return '決定者登場'; }

  function GetExplain() { return '投票が同数の時、決定者の投票先が優先されます [兼任]'; }

  function Cast(array &$list, &$rand) {
    if (RoleManager::GetStack('user_count') >= CastConfig::${$this->name}) {
      return $this->CastOnce($list, $rand);
    }
  }
}
