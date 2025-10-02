<?php
/*
  ◆権力者 (authority)
  ○仕様
  ・投票数：+1
  ・得票数補正：-2 (権力者と同じ人に投票)
*/
class Role_authority extends Role {
  public $vote_day_type = 'both';

  //投票数補正処理
  public function FilterVoteDo() {
    if ($this->CallParent('IgnoreFilterVoteDo')) return false;

    $count = $this->CallParent('GetVoteDoCount');
    if (! $this->CallParent('IsUpdateFilterVoteDo')) {
      $count += $this->GetStack('vote_number');
    }
    //Text::p($count, "◆VoteCount [$this->role]");
    $this->SetStack($count, 'vote_number');
  }

  //投票数補正無効判定
  public function IgnoreFilterVoteDo() {
    return false;
  }

  //投票数補正値取得
  public function GetVoteDoCount() {
    return 1;
  }

  //投票数上書き判定
  public function IsUpdateFilterVoteDo() {
    return false;
  }
}
