<?php
/*
  ◆舌禍狼 (tongue_wolf)
  ○仕様
  ・能力結果：襲撃
  ・襲撃：役職鑑定
*/
RoleLoader::LoadFile('wolf');
class Role_tongue_wolf extends Role_wolf {
  public $result = RoleAbility::TONGUE_WOLF;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  protected function WolfKillAction(User $user) {
    $actor = $this->GetWolfVoter();
    if (! $actor->IsActive()) return; //能力失効判定
    if ($user->IsRole('human')) $actor->LostAbility(); //村人なら能力失効
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->GetName(), $actor->id);
  }
}
