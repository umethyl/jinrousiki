<?php
/*
  ◆GMログインパスワード (gm_password)
*/
class Option_gm_password extends OptionText {
  public $type = OptionFormType::PASSWORD;

  protected function FilterEnable() {
    if (RoomOptionManager::IsChange()) {
      $this->enable = false;
    } else {
      $this->enable = GameOptionConfig::$gm_login_enable;
    }
  }

  public function GetCaption() {
    return 'GMログインパスワード';
  }

  public function GetExplain() {
    return '(仮想GMモード・クイズ村モード時のGMのパスワードです)' . Text::BR .
      '※ ログインユーザ名は「dummy_boy」です。GMは入村直後に必ず名乗ってください。';
  }

  public function GetPlaceholder() {
    return RoomManagerMessage::PLACEHOLDER_GM_PASSWORD;
  }
}
