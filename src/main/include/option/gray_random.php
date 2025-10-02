<?php
/*
  ◆グレラン村 (gray_random)
  ○仕様
  ・配役フィルタリング： 村人, 人狼, 狂人, 妖狐
*/
class Option_gray_random extends OptionCastCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'グレラン村';
  }

  protected function GetFilterCastRoleList() {
    return ['wolf', 'mad', 'fox'];
  }

  public function GetWishRole() {
    return ['human', 'wolf', 'mad', 'fox'];
  }
}
