<?php
/*
  ◆天候：雹 (hyper_random_voter)
  ○仕様
  ・投票数：ランダム増加
*/
class Event_hyper_random_voter extends Event {
  public function FilterVoteDo() {
    $vote_number = RoleManager::Stack()->Get('vote_number') + Lottery::GetRange(0, 5);
    RoleManager::Stack()->Set('vote_number', $vote_number);
  }
}
