<?php
/*
  ◆ゲーム / 投票 (game_vote)
  ○仕様
    vote         : 投票ボタンを押した or 投票ページの表示の制御用
    revote_count : 昼の再投票回数
    target_no    : 投票先の user_no (キューピッドがいるため単純に整数型にキャストしないこと)
    situation    : 投票の分類 (Kick・処刑・占い・人狼襲撃など)
*/
RQ::LoadFile('request_game_play');
class Request_game_vote extends RequestGamePlay {
  public function __construct() {
    parent::__construct();
    $this->ParsePostInt('revote_count');
    $this->ParsePostOn(RequestDataVote::ON, RequestDataVote::ADD_ACTION);
    $this->ParsePostData(RequestDataVote::TARGET, RequestDataVote::SITUATION);

    $url = $this->GetURL();
    $this->post_url = 'game_vote.php' . $url;
    $this->back_url = LinkHTML::Generate('game_up.php' . $url, Message::BACK . ' &amp; reload');
  }
}
