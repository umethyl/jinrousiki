<?php
/*
  ◆荼枳尼天 (succubus_yaksa)
  ○仕様
  ・勝利：生存 + 男性の全滅
  ・人攫い成功率低下：1/2
  ・人攫い無効：男性以外
*/
RoleLoader::LoadFile('yaksa');
class Role_succubus_yaksa extends Role_yaksa {
  protected function IgnoreOgreAssassin(User $user) {
    return ! Sex::IsMale($user);
  }

  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IsOgreLoseCamp($winner) {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return ! $this->IsActor($user) && Sex::IsMale($user);
  }
}
