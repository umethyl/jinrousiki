<?php
/*
  ◆舌禍狼 (tongue_wolf)
  ○仕様
  ・襲撃：役職が分かる
*/
RoleManager::LoadFile('wolf');
class Role_tongue_wolf extends Role_wolf {
  public $result = 'TONGUE_WOLF_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->date > 1) $this->OutputAbilityResult($this->result);
  }

  function WolfKill(User $user) {
    parent::WolfKill($user);
    $actor = $this->GetWolfVoter();
    if (! $actor->IsActive()) return; //能力失効判定
    if ($user->IsRole('human')) $actor->LostAbility(); //村人なら能力失効
    $target = DB::$USER->GetHandleName($user->uname, true);
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $target, $actor->user_no);
  }
}
