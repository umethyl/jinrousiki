<?php
/*
  ◆蛇姫 (miasma_jealousy)
  ○仕様
  ・処刑投票：熱病 (恋人 + 確率)
*/
RoleLoader::LoadFile('jealousy');
class Role_miasma_jealousy extends Role_jealousy {
  public $mix_in = ['critical_mad'];

  protected function IsVoteKillActionTarget(User $user) {
    return $user->IsRole('lovers') && Lottery::Percent(70);
  }

  protected function SetVoteKillAction(User $user) {
    $user->AddDoom(1, 'febris');
  }
}
