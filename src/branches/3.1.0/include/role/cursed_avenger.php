<?php
/*
  ◆がしゃどくろ (cursed_avenger)
  ○仕様
  ・処刑投票：死の宣告 (人外カウント)
*/
RoleLoader::LoadFile('avenger');
class Role_cursed_avenger extends Role_avenger {
  public $mix_in = array('critical_mad');

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IsVoteKillActionTarget(User $user) {
    return RoleUser::IsInhuman($user);
  }

  protected function SetVoteKillAction(User $user) {
    $user->AddDoom(4);
  }
}
