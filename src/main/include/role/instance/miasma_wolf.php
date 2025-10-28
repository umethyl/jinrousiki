<?php
/*
  ◆戯狼 (miasma_wolf)
  ○仕様
  ・処刑得票：熱病 (自身も含む) / 処刑時も有効
*/
RoleLoader::LoadFile('wolf');
class Role_miasma_wolf extends Role_wolf {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      //自身に悪戯が付与されていたら発動
      if (false === RoleUser::AvoidLovers($user)) {
	$this->VoteKillBadStatusReaction($user);
      }

      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$target = DB::$USER->ByRealUname($target_uname);
	if (false === RoleUser::Avoid($target)) {
	  $this->VoteKillBadStatusReaction($target, true);
	}
      }
    }
  }

  //処刑時悪戯カウンター
  private function VoteKillBadStatusReaction(User $user, bool $virtual = false) {
    if ($user->IsDead(true)) {
      return;
    }

    //悪戯(サブ)は仮想ユーザーで判定する (自身は憑依追跡不要)
    $target = (true === $virtual) ? $user->GetVirtual() : $user;
    foreach ($target->GetPartner('bad_status', true) as $id => $date) { //悪戯有効判定
      if (true === DateBorder::On($date)) {
	$target->AddDoom(1, 'febris');
	break;
      }
    }
  }
}
