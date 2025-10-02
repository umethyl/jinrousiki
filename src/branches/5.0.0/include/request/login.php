<?php
/*
  ◆ゲーム / ログイン (login)
  ○仕様
    room_no, password, trip, uname : 村・ユーザ情報 (user_entry)
    login_manually : 実行タイプ
*/
class Request_login extends Request {
  public function __construct() {
    Text::EncodePost();
    $this->ParseGetInt(RequestDataGame::ID);
    $this->ParsePostOn(RequestDataUser::LOGIN);
    $this->ParsePostStr(RequestDataUser::PASSWORD);
    $this->ParsePostData(RequestDataUser::TRIP);
    $this->ParsePost('Trip', RequestDataUser::UNAME);
  }
}
