<?php
/*
  ◆権力者登場 (authority)
  ○仕様
  ・配役配布：誰か一人に権力者
*/
class Option_authority extends CheckRoomOptionItem {
  public function GetCaption() {
    return '権力者登場';
  }

  public function GetExplain() {
    return '投票の票数が二票になります [兼任]';
  }

  public function Cast() {
    if (Cast::Stack()->Get('user_count') >= CastConfig::${$this->name}) {
      return $this->CastOnce();
    }
  }
}
