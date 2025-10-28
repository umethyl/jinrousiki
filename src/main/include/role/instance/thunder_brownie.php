<?php
/*
  ◆雷公 (thunder_brownie)
  ○仕様
  ・落雷：再投票の最多得票者
  ・ショック死：落雷対象者
*/
class Role_thunder_brownie extends Role {
  public $mix_in = ['chicken'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  //落雷判定
  public function SetThunderbolt() {
    $stack = $this->GetStack();
    if (false === is_array($stack) || $this->DetermineVoteKill()) {
      return;
    }

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
      if ($user->IsLive(true) && false === RoleUser::Avoid($user, true)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, '◆ThunderboltBase');

    //$actor は直前に別フィルタで設定されたユーザが入るケースがあるので注意
    $this->AddStackName(DB::$USER->ByVirtual(Lottery::Get($stack))->uname, 'thunderbolt');
  }

  protected function IsSuddenDeath() {
    return in_array($this->GetUname(), RoleManager::Stack()->Get('thunderbolt'));
  }

  protected function GetSuddenDeathType() {
    return 'THUNDERBOLT';
  }
}
