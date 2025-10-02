<?php
/*
  ◆ろくろ首 (prince)
  ○仕様
  ・処刑キャンセル：恋人陣営以外
*/
class Role_prince extends Role {
  //処刑キャンセル
  public function VoteKillCancel() {
    $user = $this->GetActor();
    if (false === $user->IsActive() || $user->IsWinCamp(Camp::LOVERS)) {
      return;
    }

    $user->UpdateLive(UserLive::LIVE);
    $user->Flag()->On(UserMode::REVIVE);
    $user->LostAbility();
    DB::$ROOM->StoreDead($user->handle_name, DeadReason::VOTE_CANCELLED);
  }
}
