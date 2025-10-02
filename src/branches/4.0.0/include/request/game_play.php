<?php
/*
  ◆ゲーム / 本体 (game_play)
  ○仕様
    dead_mode, heave_mode : モード判定
    set_objection: 異議あり
    say, font_type : 発言
*/
RQ::LoadFile('request_game_play');
class Request_game_play extends RequestGamePlay {
  public function __construct() {
    Text::EncodePost();
    parent::__construct();
    $this->ParseGetOn(RequestDataRoom::DEAD, RequestDataRoom::HEAVEN);
    $this->ParsePostOn(RequestDataTalk::OBJECTION);
    $this->ParsePostStr(RequestDataTalk::VOICE);
    $this->ParsePostData(RequestDataTalk::SENTENCE);
    $this->last_words = (TalkVoice::LAST_WORDS == $this->font_type);
  }
}
