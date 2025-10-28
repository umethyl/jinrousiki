<?php
/*
  ◆ゲーム / 親フレーム (game_frame)
  ○仕様
    dead_mode : 霊界モード
*/
RQ::LoadFile('request_game_play');
class Request_game_frame extends RequestGamePlay {
  public function __construct() {
    parent::__construct();
    $this->ParseGetOn(RequestDataRoom::DEAD);
    $this->url = $this->GetURL(true);
  }
}
