<?php
/*
  ◆扇動者 (agitate_mad)
  ○仕様
  ・処刑者決定：同一投票先 + 残りをまとめてショック死
*/
class Role_agitate_mad extends Role {
  public $mix_in = ['chicken', 'decide'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function DecideVoteKill() {
    if ($this->DecideVoteKillSame()) return;

    $uname = $this->GetVoteKill();
    foreach ($this->GetStack(VoteDayElement::MAX_VOTED) as $target_uname) {
      if ($target_uname != $uname) { //$target_uname は仮想ユーザ
	$this->SuddenDeathKill(DB::$USER->ByRealUname($target_uname)->id);
      }
    }
  }

  protected function GetSuddenDeathType() {
    return 'AGITATED';
  }
}
