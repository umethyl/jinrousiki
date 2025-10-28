<?php
/*
  ◆game 用共通クラス (Game)
  ○仕様
    db_no       : DB名 (DatabaseConfig::$name_list)
    room_no     : 番地 (room.room_no)
    auto_realod : 自動更新 (GameConfig::$auto_reload_list)
*/
class RequestGame extends Request {
  public function __construct() {
    $this->ParseGetInt(RequestDataGame::DB, RequestDataGame::ID, RequestDataGame::RELOAD);
    $min = min(GameConfig::$auto_reload_list);
    if ($this->auto_reload != 0 && $this->auto_reload < $min) {
      $this->auto_reload = $min;
    }
    $this->add_role = null;
  }
}
