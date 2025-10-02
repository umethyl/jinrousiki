<?php
/*
  ◆荼枳尼天 (succubus_yaksa)
  ○仕様
  ・勝利：生存 + 男性の全滅
  ・人攫い無効：男性以外
*/
RoleManager::LoadFile('yaksa');
class Role_succubus_yaksa extends Role_yaksa {
  public $reduce_rate = 2;

  function Win($winner) {
    if ($this->IsDead()) return false;
    foreach (DB::$USER->rows as $user) {
      if (! $this->IsActor($user) && $user->IsLive() && $user->IsMale()) return false;
    }
    return true;
  }

  protected function IgnoreAssassin(User $user) { return ! $user->IsMale(); }
}
