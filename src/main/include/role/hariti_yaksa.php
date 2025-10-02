<?php
/*
  ◆鬼子母神 (hariti_yaksa)
  ○仕様
  ・勝利：生存 + 子狐系・キューピッド系・天使系全滅 + 村人陣営以外勝利
  ・人攫い成功率低下：1/2
*/
RoleLoader::LoadFile('yaksa');
class Role_hariti_yaksa extends Role_yaksa {
  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::HUMAN;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsMainGroup(CampGroup::CHILD_FOX, CampGroup::CUPID, CampGroup::ANGEL);
  }
}
