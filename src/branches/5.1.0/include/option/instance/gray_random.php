<?php
/*
  ◆グレラン村 (gray_random)
  ○仕様
  ・追加配役(特殊配役村)： 有効
  ・配役フィルタリング： 村人, 人狼, 狂人, 妖狐, 背徳者
*/
class Option_gray_random extends OptionCastCheckbox {
  public $group = OptionGroup::GAME;

  public function GetCaption() {
    return 'グレラン村';
  }

  protected function EnableFilterCastAddRoleSpecial() {
    return true;
  }

  protected function GetFilterCastRoleList() {
    return ['wolf', 'mad', 'fox', 'depraver'];
  }

  public function GetWishRole() {
    return ['human', 'wolf', 'mad', 'fox', 'depraver'];
  }
}
