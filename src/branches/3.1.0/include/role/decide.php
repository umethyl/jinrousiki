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
    if ($this->IsVoteKill()) return;

    $target = $this->GetStack();
    if (in_array($target, $this->GetVotePossible())) {
      $this->SetVoteKill($target);
    }
  }

  //処刑者候補取得
  final protected function GetVotePossible() {
    return $this->GetStack('vote_possible');
  }

  //最大得票者投票者ユーザ名取得
  final protected function GetMaxVotedUname() {
    return array_intersect($this->GetVotePossible(), $this->GetStack());
  }

  //処刑者ユーザ名登録
  final protected function SetVoteKill($uname) {
    return $this->SetStack($uname, 'vote_kill_uname');
  }

  //単一処刑者候補判定
  final protected function DecideVoteKillSame() {
    if ($this->IsVoteKill() || ! is_array($this->GetStack())) return true;

    $stack = $this->GetMaxVotedUname();
    if (count($stack) != 1) return true;

    $this->SetVoteKill(array_shift($stack));
    return false;
  }
}
