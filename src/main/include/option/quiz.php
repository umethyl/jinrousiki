<?php
/*
  ◆クイズ村 (quiz)
  ○仕様
  ・配役フィルタリング： 村人, 共有者, 人狼, 狂人, 妖狐
  ・配役フィルタリング置換：村人 -> 出題者
  ・配役：解答者付加 (出題者以外)
*/
class Option_quiz extends OptionCastCheckbox {
  public function GetCaption() {
    return 'クイズ村';
  }

  public function GetExplain() {
    return 'GM が出題者になり、プレイヤー全員に回答者がつきます。';
  }

  protected function GetFilterCastRoleList() {
    return ['common', 'wolf', 'mad', 'fox'];
  }

  protected function ReplaceFilterCast(array $role_list) {
    ArrayFilter::Replace($role_list, 'human', $this->name);
    return $role_list;
  }

  public function GetCastDummyBoyFixRole(array $list) {
    return $this->name;
  }

  protected function IgnoreCastUserSubRoleAll($id) {
    $stack = Cast::Stack()->Get(Cast::UNAME);
    return $stack[$id] == GM::DUMMY_BOY;
  }

  protected function GetCastUserSubRoleAllRole($id) {
    return 'panelist';
  }

  protected function GetResultCastUserSubRoleList() {
    return ['panelist'];
  }

  public function GetWishRole() {
    return ['mad', 'common', 'fox'];
  }
}
