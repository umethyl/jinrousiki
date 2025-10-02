<?php
/*
  ◆GM ログインパスワード (gm_password)
*/
class Option_gm_password extends OptionText {
  public $type = OptionFormType::PASSWORD;

  protected function FilterEnable() {
    if (OptionManager::IsChange()) {
      $this->enable = false;
    } else {
      $this->enable = GameOptionConfig::$gm_login_enable;
    }
  }

  public function GetCaption() {
    return 'GM ログインパスワード';
  }

  public function GetExplain() {
    return '(仮想 GM モード・クイズ村モード時の GM のパスワードです)' . Text::BR .
      '※ ログインユーザ名は「dummy_boy」です。GM は入村直後に必ず名乗ってください。';
  }
}
