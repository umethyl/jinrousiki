<?php
/*
  ◆グレラン村 (gray_random)
  ○仕様
  ・配役：村人, 人狼, 狂人, 妖狐
*/
class Option_gray_random extends OptionCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'グレラン村';
  }

  public function SetFilterRole($count) {
    return Cast::FilterRole($count, ['wolf', 'mad', 'fox']);
  }

  public function GetWishRole() {
    return ['human', 'wolf', 'mad', 'fox'];
  }
}
