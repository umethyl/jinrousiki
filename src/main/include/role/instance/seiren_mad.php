<?php
/*
  ◆海歌姫 (seiren_mad)
  ○仕様
  ・悪戯：サブ役職付加 (惑溺 / 3の倍数日)
*/
class Role_seiren_mad extends Role {
  public $mix_in = ['vote' => 'fairy'];

  protected function IsFairyVote() {
    return DateBorder::Third() && Number::MultipleThree(DB::$ROOM->date);
  }

  protected function GetDisabledFairyVoteNightMessage() {
    return VoteRoleMessage::IMPOSSIBLE_VOTE_DAY;
  }

  protected function FairyAction(User $user) {
    $user->AddRole('infatuated');
  }
}
