<?php
/*
  ◆天候：濃霧 (psycho_infected)
  ○仕様
  ・処刑投票妨害：洗脳者付加 (ランダム)
*/
class Event_psycho_infected extends Event {
  public function VoteKillAction() {
    $stack = [];
    foreach (RoleManager::Stack()->Get(VoteDayElement::USER_LIST) as $id => $uname) {
      $user = DB::$USER->ByID($id);
      if ($user->IsLive(true) && ! RoleUser::IsAvoid($user, true) &&
	  ! $user->IsRole($this->name) && ! $user->IsCamp(Camp::VAMPIRE)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, '◆Target [psycho_infected]');
    DB::$USER->ByID(Lottery::Get($stack))->AddRole($this->name);
  }
}
