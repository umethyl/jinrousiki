<?php
/*
  ◆冥狐 (doom_fox)
  ○仕様
  ・暗殺：死の宣告 (4日後)
*/
RoleManager::LoadFile('fox');
class Role_doom_fox extends Role_fox {
  public $mix_in = 'assassin';

  public function OutputAction() {
    $this->filter->OutputAction();
  }

  public function IsVote() {
    return $this->filter->IsVote();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
  }

  public function IsFinishVote(array $list) {
    return $this->filter->IsFinishVote($list);
  }

  public function Assassin(User $user) {
    if ($user->IsLive(true)) $user->AddDoom(4, 'death_warrant');
  }
}
