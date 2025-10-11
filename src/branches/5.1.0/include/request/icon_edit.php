<?php
/*
  ◆アイコン編集 (icon_edit)
  ○仕様
    disable  : 非表示
    password : 編集パスワード
*/
RQ::LoadFile('request_icon');
class Request_icon_edit extends RequestIcon {
  public function __construct() {
    parent::__construct();
    $this->ParsePostOn('disable');
    $this->ParsePostStr(RequestDataIcon::PASSWORD);
    $this->ParsePostOn(RequestDataIcon::MULTI);
    if ($this->Enable(RequestDataIcon::MULTI)) {
      $this->ParsePostStr(RequestDataIcon::NUMBER);
    }
  }
}
