<?php
/*
  ◆決定者 (decide)
  ○仕様
  ・役職表示：無し
  ・処刑者決定：自分の投票先
*/
class Role_decide extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::TARGET;
  }

  //処刑者決定
  public function DecideVoteKill() {
    if ($this->DetermineVoteKill()) {
      return;
    }

    $target = $this->GetStack();
    if (in_array($target, $this->GetVoteKillPossibleList())) {
      $this->SetVoteKill($target);
    }
  }

  //処刑者候補取得
  final protected function GetVoteKillPossibleList() {
    return $this->GetStack(VoteDayElement::VOTE_POSSIBLE);
  }

  //決定能力者の処刑者候補リスト取得
  final protected function GetDecideVoteKillPossibleList() {
    return array_intersect($this->GetVoteKillPossibleList(), $this->GetStack());
  }

  //処刑者ユーザ名登録
  final protected function SetVoteKill($uname) {
    return $this->SetStack($uname, VoteDayElement::VOTE_KILL);
  }

  //処刑者決定 (単一処刑者候補投票判定)
  final protected function DecideVoteKillSame() {
    if ($this->DetermineVoteKill() || false === is_array($this->GetStack())) {
      return true;
    }

    $stack = $this->GetDecideVoteKillPossibleList();
    if (count($stack) != 1) {
      return true;
    }

    $this->SetVoteKill(array_shift($stack));
    return false;
  }
}
