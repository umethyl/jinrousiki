<?php
/*
  ◆アイコン登録 (icon_upload)
  ○仕様
    size, type, tmp_name : アイコンファイル情報
    command : 実行タイプ
*/
RQ::LoadFile('request_icon');
class Request_icon_upload extends RequestIcon {
  public function __construct() {
    if (Security::IsInvalidValue($_FILES)) {
      die();
    }

    parent::__construct();
    $this->Parse('file', 'intval', ['size']);
    $this->Parse('file', null, ['type', 'tmp_name']);
    $this->ParsePostData('command');
  }
}
