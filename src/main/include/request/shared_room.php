<?php
/*
  ◆関連サーバ村情報 (shared_room)
  ○仕様
*/
class Request_shared_room extends Request {
  public function __construct() {
    $this->ParseGetInt('id');
  }
}
