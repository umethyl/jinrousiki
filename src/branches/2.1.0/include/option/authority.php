<?php
/*
  ◆権力者登場 (authority)
  ○仕様
  ・配役配布：誰か一人に権力者
*/
class Option_authority extends CheckRoomOptionItem {
  function GetCaption() { return '権力者登場'; }

  function GetExplain() { return '投票の票数が二票になります [兼任]'; }

  function Cast(array &$list, &$rand) {
    if (RoleManager::$get->user_count >= CastConfig::${$this->name}) {
      return $this->CastOnce($list, $rand);
    }
  }
}
