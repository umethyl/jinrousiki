<?php
/*
  ◆サブ役職制限なし
*/
class Option_sub_role_limit_none extends OptionCheckbox {
  public $type = OptionFormType::RADIO;

  public function GetCaption() {
    return 'サブ役職制限なし';
  }

  protected function GetURL() {
    return 'chaos.php#' . $this->name;
  }

  //配役対象サブ役職取得
  public function GetCastSubRoleList() {
    $list = 'chaos_' . $this->name . '_list';
    return isset(ChaosConfig::$$list) ? ChaosConfig::$$list : array();
  }
}
