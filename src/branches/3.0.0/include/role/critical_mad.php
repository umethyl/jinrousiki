<?php
/*
  ◆釣瓶落とし (critical_mad)
  ○仕様
  ・処刑投票：痛恨
*/
class Role_critical_mad extends Role {
  public $vote_day_type = 'init';

  public function VoteAction() {
    $class = $this->GetParent($method = 'SetVoteAction');
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsLive(true)) $class->$method($user);
    }
  }

  public function SetVoteAction(User $user) {
    if (! $user->IsAvoid()) $user->AddRole('critical_luck');
  }
}
