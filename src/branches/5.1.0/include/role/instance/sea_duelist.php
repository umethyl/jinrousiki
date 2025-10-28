<?php
/*
  ◆海御前 (sea_duelist)
  ○仕様
  ・処刑投票：退治 (宿敵限定)
  ・自分撃ち：固定
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_sea_duelist extends Role_valkyrja_duelist {
  public $mix_in = ['chicken'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    $stack = []; //ショック死対象者リスト
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($uname) || $this->IsVoteKill($target_uname)) {
	continue;
      }

      $user   = DB::$USER->ByUname($uname);
      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsPartner($this->GetPartnerRole(), $user->id)) {
	$stack[$user->id] = true;
      }
    }

    foreach ($stack as $id => $flag) { //ショック死処理
      $this->SuddenDeathKill($id);
    }
  }

  protected function GetSuddenDeathType() {
    return 'DUEL';
  }

  protected function FixSelfShoot() {
    return true;
  }
}
