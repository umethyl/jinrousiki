<?php
/*
  ◆ゲーム / 発言 (game_up)
  ○仕様
    dead_mode, heave_mode : モード判定
*/
RQ::LoadFile('request_game_play');
class Request_game_up extends RequestGamePlay {
  public function __construct() {
    parent::__construct();
    $this->ParseGetOn(RequestDataRoom::DEAD, RequestDataRoom::HEAVEN);

    $url = $this->GetURL(true);
    foreach ([RequestDataRoom::DEAD, RequestDataRoom::HEAVEN] as $key) {
      $url .= $this->ToURL($key);
    }
    $this->url = $url;
  }
}
