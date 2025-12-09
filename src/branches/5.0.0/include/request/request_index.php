<?php
/*
  ◆トップページ (request_index)
  ○仕様
    ディレクトリインデックスとの誤認識回避のため、冗長な名称を使用する
*/
class Request_request_index extends Request {
  public $room_no;
  public $id;
  public $create_room;
  public $change_room;
  public $describe_room;

  public function __construct() {
    $this->ParseGetInt('id');
  }
}
