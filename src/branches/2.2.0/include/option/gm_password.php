<?php
/*
  ◆GM ログインパスワード (gm_password)
*/
class Option_gm_password extends TextRoomOptionItem {
  public $type = 'password';

  function __construct() {
    parent::__construct();
    if (OptionManager::$change) {
      $this->enable = false;
    } else {
      $this->enable = GameOptionConfig::$gm_login_enable;
    }
  }

  function GetCaption() { return 'GM ログインパスワード'; }

  function GetExplain() {
    return '(仮想 GM モード・クイズ村モード時の GM のパスワードです)<br>' .
      '※ ログインユーザ名は「dummy_boy」です。GM は入村直後に必ず名乗ってください。';
  }
}
