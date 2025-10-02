<?php
/*
  ◆村作成 (room_manager)
  ○仕様
*/
class Request_room_manager extends Request {
  public function __construct() {
    Text::EncodePost();
    $this->ParseGetInt('room_no');
    $this->ParsePostOn('create_room', 'change_room');
    $this->ParseGetOn('describe_room');
  }
}
