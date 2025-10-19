<?php
/*
  ◆封印師 (seal_medium)
  ○仕様
  ・処刑投票：封印 (限定能力所持者 & 人外)
*/
RoleLoader::LoadFile('medium');
class Role_seal_medium extends Role_medium {
  public $mix_in = ['critical_mad', 'chicken'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IgnoreVoteKillAction(User $user) {
    return RoleUser::AvoidLovers($user, true);
  }

  protected function IsVoteKillActionTarget(User $user) {
    return $user->IsRole($this->GetSealList());
  }

  //封印対象役職取得
  private function GetSealList() {
    return [
      'phantom_wolf', 'resist_wolf', 'revive_wolf', 'step_wolf', 'tongue_wolf',
      'trap_mad', 'possessed_mad', 'revive_mad',
      'phantom_fox', 'spell_fox', 'emerald_fox', 'revive_fox', 'possessed_fox', 'trap_fox',
      'revive_cupid',
      'revive_avenger'
    ];
  }

  protected function SetVoteKillAction(User $user) {
    $user->IsActive() ? $user->LostAbility() : $this->SuddenDeathKill($user->id);
  }

  protected function GetSuddenDeathType() {
    return 'SEALED';
  }
}
