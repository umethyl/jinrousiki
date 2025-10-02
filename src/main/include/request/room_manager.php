<?php
/*
  ◆村作成 (room_manager)
  ○仕様
*/
class Request_room_manager extends Request {
  //プロパティを直接参照 + bool 想定で判定しているので返り値を調整
  public function __get($name) {
    $this->$name = false;
    return $this->$name;
  }

  public function __construct() {
    Text::EncodePost();
    $this->ParseGetInt('room_no');
    $this->ParsePostOn('create_room', 'change_room');
    $this->ParseGetOn('describe_room');
  }
}
