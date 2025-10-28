<?php
/*
  ◆花魁 (sacrifice_cupid)
  ○仕様
  ・仲間表示：洗脳者
  ・自分撃ち：固定
  ・処刑投票：洗脳者付加
  ・人狼襲撃：身代わり
*/
RoleLoader::LoadFile('cupid');
class Role_sacrifice_cupid extends Role_cupid {
  public $mix_in = ['protected'];

  protected function OutputAddPartner() {
    $stack = [];
    foreach (DB::$USER->GetRoleUser('psycho_infected') as $user) {
      $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, 'psycho_infected_list');
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsDead(true)) {
	continue;
      }

      //吸血鬼判定
      if ($user->IsMainGroup(CampGroup::VAMPIRE) ||
	  (RoleUser::IsDelayCopy($user) && $user->IsCamp(Camp::VAMPIRE))) {
	continue;
      }
      $user->AddRole('psycho_infected');
    }
  }

  protected function FixSelfShoot() {
    return true;
  }

  protected function IsSacrifice(User $user) {
    return false === $this->IsActor($user) && $user->IsRole('psycho_infected');
  }
}
