<?php
/*
  ◆釣瓶落とし (critical_mad)
  ○仕様
  ・処刑投票：痛恨付加
*/
class Role_critical_mad extends Role {
  function SetVoteDay($uname) {
    $this->InitStack();
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  function VoteAction() {
    $class = $this->GetClass($method = 'SetVoteAction');
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsLive(true)) $class->$method($user);
    }
  }

  function SetVoteAction(User $user) {
    if (! $user->IsAvoid()) $user->AddRole('critical_luck');
  }
}
