<?php
/*
  ◆天候：雪 (frostbite)
  ○仕様
  ・処刑投票妨害：凍傷付加 (ランダム)
*/
class Event_frostbite extends Event {
  public function VoteKillAction() {
    $stack = [];
    foreach (RoleManager::Stack()->Get(VoteDayElement::USER_LIST) as $id => $uname) {
      $user = DB::$USER->ByID($id);
      if ($user->IsLive(true) && ! RoleUser::IsAvoid($user, true) &&
	  ! $user->IsDoomRole($this->name)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, '◆Target [frostbite]');
    DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, $this->name);
  }
}
