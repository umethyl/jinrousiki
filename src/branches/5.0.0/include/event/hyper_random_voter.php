<?php
/*
  ◆天候：雹 (hyper_random_voter)
  ○仕様
  ・投票数：ランダム増加
*/
class Event_hyper_random_voter extends Event {
  public function FilterVoteDo() {
    $count = RoleManager::Stack()->Get(VoteDayElement::VOTE_NUMBER) + Lottery::GetRange(0, 5);
    RoleManager::Stack()->Set(VoteDayElement::VOTE_NUMBER, $count);
  }
}
