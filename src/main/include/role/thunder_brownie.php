<?php
/*
  ◆雷公 (thunder_brownie)
  ○仕様
*/
class Role_thunder_brownie extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  //落雷判定
  public function SetThunderbolt() {
    $stack = $this->GetStack();
    if (false === is_array($stack) || $this->IsVoteKill()) return;

    $target_list = $this->GetStack(VoteDayElement::VOTE_POSSIBLE);
    if (count(array_intersect($target_list, array_keys($stack))) > 0) {
      $this->SetThunderboltTarget();
    }
  }

  //落雷対象者選出
  public function SetThunderboltTarget() {
    $stack = [];
    foreach (RoleManager::Stack()->Get(VoteDayElement::USER_LIST) as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsLive(true) && ! RoleUser::IsAvoid($user, true)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, '◆ThunderboltBase');
    //$actor は直前に別フィルタで設定されたユーザが入るケースがあるので注意
    $this->AddStackName(DB::$USER->ByVirtual(Lottery::Get($stack))->uname, 'thunderbolt');
  }
}
