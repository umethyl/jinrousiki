<?php
/*
  ◆統計情報 (statistics)
  ○仕様
*/
class Request_statistics extends Request {
  public function __construct() {
    $this->ParseGetInt(RequestDataGame::DB);
  }
}
