<?php
/*
  ◆ろくろ首 (prince)
  ○仕様
  ・処刑キャンセル：恋人陣営以外
*/
class Role_prince extends Role {
  //処刑キャンセル
  public function VoteCancel(User $user) {
    if (! $user->IsActive() || $user->IsCamp('lovers', true)) return;
    $user->UpdateLive(UserLive::LIVE);
    $user->revive_flag = true;
    $user->LostAbility();
    DB::$ROOM->ResultDead($user->handle_name, 'VOTE_CANCELLED');
  }
}
