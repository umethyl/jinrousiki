<?php
/*
  ◆権力者 (authority)
  ○仕様
  ・投票数：+1
  ・得票数補正：-2 (反逆者と同じ人に投票)
  ・処刑投票情報収集：自分と対象者
*/
class Role_authority extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ETC;
  }

  protected function SetStackVoteKillEtc($uname) {
    $this->SetStack($this->GetUname());
    $this->SetStack($uname, $this->role . '_uname');
  }

  //投票数補正
  final public function FilterVoteDo() {
    if ($this->CallParent('IgnoreFilterVoteDo')) {
      return false;
    }

    $count = $this->CallParent('GetVoteDoCount');
    if ($this->CallParent('EnableAddVoteDoCount')) {
      $count += $this->GetStack(VoteDayElement::VOTE_NUMBER);
    }
    $this->CallParent('NoticeFilterVoteDo');
    //Text::p($count, "◆VoteCount [$this->role]");

    $this->SetStack($count, VoteDayElement::VOTE_NUMBER);
  }

  //投票数補正無効判定
  protected function IgnoreFilterVoteDo() {
    return false;
  }

  //投票数補正値取得
  protected function GetVoteDoCount() {
    return 1;
  }

  //投票数加算有効判定
  protected function EnableAddVoteDoCount() {
    return true;
  }

  //投票数補正通知
  protected function NoticeFilterVoteDo() {}
}
