<?php
/*
  ◆榊鬼 (poison_ogre)
  ○仕様
  ・勝利：出題者陣営勝利 or 生存
  ・人攫い成功率低下：1/3
  ・人攫い無効：出題者
  ・人攫い：解答者付加
  ・毒：人外カウント or 鬼陣営
*/
RoleLoader::LoadFile('ogre');
class Role_poison_ogre extends Role_ogre {
  public $mix_in = ['poison'];

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsInhuman($user) || $user->IsMainCamp(Camp::OGRE);
  }

  protected function IgnoreOgreAssassin(User $user) {
    return $user->IsRole('quiz');
  }

  protected function GetOgreReduceDenominator() {
    return 3;
  }

  protected function OgreAssassin(User $user) {
    $user->AddRole('panelist');
  }

  protected function IsOgreWinCamp($winner) {
    return $winner == Camp::QUIZ;
  }

  protected function IsOgreLoseLive() {
    return false;
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }

  protected function OgreWin() {
    return $this->IsActorLive();
  }
}
