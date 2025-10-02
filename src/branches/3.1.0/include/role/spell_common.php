<?php
/*
  ◆葛の葉 (spell_common)
  ○仕様
 ・処刑投票：魔が言 (人外カウント or 恋人)
*/
RoleLoader::LoadFile('common');
class Role_spell_common extends Role_common {
  public $mix_in = array('critical_mad');

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IgnoreVoteKillAction(User $user) {
    return false;
  }

  protected function IsVoteKillActionTarget(User $user) {
    return RoleUser::IsInhuman($user) || $user->IsRole('lovers');
  }

  protected function GetVoteKillActionRole() {
    return 'cute_camouflage';
  }
}
