<?php
/*
  ◆金剛夜叉 (vajra_yaksa)
  ○仕様
  ・勝利：生存 + 蘇生能力者全滅 + 村人陣営以外勝利
  ・人攫い成功率低下：1/3
*/
RoleLoader::LoadFile('yaksa');
class Role_vajra_yaksa extends Role_yaksa {
  protected function GetOgreReduceDenominator() {
    return 3;
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::HUMAN;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsMainGroup(CampGroup::POISON_CAT) || $user->IsRoleGroup('revive') ||
      $user->IsRole('scarlet_vampire', 'resurrect_mania');
  }
}
