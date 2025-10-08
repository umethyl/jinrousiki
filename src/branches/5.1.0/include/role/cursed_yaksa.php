<?php
/*
  ◆滝夜叉姫 (cursed_yaksa)
  ○仕様
  ・勝利：生存 + 占い師系・魔法使い系全滅
  ・人攫い成功率低下：1/3
  ・人攫い無効：占い師系・魔法使い系以外
*/
RoleLoader::LoadFile('yaksa');
class Role_cursed_yaksa extends Role_yaksa {
  protected function GetOgreReduceDenominator() {
    return 3;
  }

  protected function IsOgreLoseCamp($winner) {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsMainGroup(CampGroup::MAGE, CampGroup::WIZARD);
  }
}
