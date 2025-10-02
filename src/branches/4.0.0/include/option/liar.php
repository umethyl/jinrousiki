<?php
/*
  ◆狼少年村 (liar)
  ○仕様
  ・配役：狼少年 / 全員 (ランダム)
*/
class Option_liar extends OptionCheckbox {
  public function GetCaption() {
    return '狼少年村';
  }

  public function GetExplain() {
    return 'ランダムで「狼少年」がつきます';
  }

  protected function IgnoreCastAll($id) {
    return ! Lottery::Percent(70);
  }
}
