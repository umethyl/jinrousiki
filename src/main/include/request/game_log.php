<?php
/*
  ◆ゲーム / ログ (game_log)
  ○仕様
    村情報 : room_no, date, scene (room)
    ユーザ情報 : user_no (user_entry)
*/
class Request_game_log extends Request {
  public function __construct() {
    $this->ParseGetRoomNo();
    $this->ParseGetInt('date', RequestDataUser::ID);
    $this->ParseGetData('scene');
    if ($this->IsInvalidScene()) {
      HTML::OutputResult(Message::REQUEST_ERROR, Message::REQUEST_ERROR);
    }
  }

  private function IsInvalidScene() {
    switch ($this->scene) {
    case RoomScene::BEFORE:
      return $this->date != 0;

    case RoomScene::DAY:
    case RoomScene::NIGHT:
      return $this->date < 1;

    case RoomScene::AFTER:
    case RoomScene::HEAVEN:
      return false;

    default:
      return true;
    }
  }
}
