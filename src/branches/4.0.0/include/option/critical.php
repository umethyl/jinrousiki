<?php
/*
  ◆急所村 (critical)
  ○仕様
  ・配役：全員に会心・痛恨
*/
class Option_critical extends OptionCheckbox {
  public function GetCaption() {
    return '急所村';
  }

  public function GetExplain() {
    return '全員に「会心」「痛恨」がつきます。';
  }

  protected function GetCastAllRole($id) {
    return 'critical_voter critical_luck';
  }

  protected function GetResultCastList() {
    return ['critical_voter', 'critical_luck'];
  }
}
