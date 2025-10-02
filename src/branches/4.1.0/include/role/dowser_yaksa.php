<?php
/*
  ◆毘沙門天 (dowser_yaksa)
  ○仕様
  ・勝利：生存 + 自分よりサブ役職の所持数が多い人の全滅
  ・人攫い成功率低下：1/2
  ・人攫い無効：サブ役職未所持
  ・人狼襲撃無効確率：40%
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('yaksa');
class Role_dowser_yaksa extends Role_yaksa {
  protected function GetOgreResistWolfEatRate() {
    return 40;
  }

  public function GetReflectAssassinRate() {
    return 40;
  }

  protected function IgnoreSetOgreAssassin(User $user) {
    return $user->GetRoleCount() == 1;
  }

  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IsOgreLoseCamp($winner) {
    return false;
  }

  protected function IgnoreOgreLoseSurvive() {
    return true;
  }

  protected function OgreWin() {
    $count = $this->GetActor()->GetRoleCount();
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $user->GetRoleCount() > $count) {
	return false;
      }
    }
    return true;
  }
}
