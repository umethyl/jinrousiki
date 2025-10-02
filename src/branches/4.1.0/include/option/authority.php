<?php
/*
  ◆権力者登場 (authority)
  ○仕様
  ・配役配布：誰か一人に権力者
*/
class Option_authority extends OptionCheckbox {
  public function GetCaption() {
    return '権力者登場';
  }

  public function GetExplain() {
    return '投票の票数が二票になります [兼任]';
  }

  public function CastUserSubRole() {
    if (Cast::Stack()->Get(Cast::COUNT) >= CastConfig::${$this->name}) {
      return $this->CastUserSubRoleOnce();
    }
  }
}
