<?php
/*
  ◆クイズ村 (quiz)
  ○仕様
  ・配役：解答者付加 (出題者以外)
*/
class Option_quiz extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'クイズ村';
  }

  public function GetExplain() {
    return 'GM が出題者になり、プレイヤー全員に回答者がつきます。';
  }

  public function SetFilterRole($count) {
    $stack = Cast::FilterRole($count, array('common', 'wolf', 'mad', 'fox'));
    ArrayFilter::Replace($stack, 'human', $this->name);
    return $stack;
  }

  protected function IgnoreCastAll($id) {
    $stack = Cast::Stack()->Get(Cast::UNAME);
    return $stack[$id] == GM::DUMMY_BOY;
  }

  protected function GetCastAllRole($id) {
    return 'panelist';
  }

  protected function GetResultCastList() {
    return array('panelist');
  }

  public function GetWishRole() {
    return array('mad', 'common', 'fox');
  }
}
