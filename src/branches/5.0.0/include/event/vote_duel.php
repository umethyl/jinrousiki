<?php
/*
  ◆イベント：決選投票 (vote_duel)
  ○仕様
  ・決選投票：処刑投票対象限定
*/
class Event_vote_duel extends Event {
  public function VoteDuel() {
    //Text::p(RQ::Get()->target_no, "◆Target [$this->name]");
    if (false === DB::$ROOM->Stack()->IsInclude($this->name, RQ::Get()->target_no)) {
      VoteHTML::OutputResult(VoteMessage::VOTE_DUEL);
    }
  }
}
