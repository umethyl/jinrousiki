<?php
/*
  ◆餓狼 (hungry_wolf)
  ○仕様
  ・仲間襲撃：可能
  ・襲撃無効判定：なし
  ・襲撃：人外カウントのみ
  ・人狼襲撃死因：餓狼襲撃
*/
RoleLoader::LoadFile('wolf');
class Role_hungry_wolf extends Role_wolf {
  protected function IsWolfEatTarget($id) {
    return true;
  }

  protected function IgnoreDisableWolfEat() {
    return true;
  }

  public function WolfEatAction(User $user) {
    return false === RoleUser::IsInhuman($user);
  }

  protected function GetWolfKillReason() {
    return DeadReason::HUNGRY_WOLF_KILLED;
  }
}
