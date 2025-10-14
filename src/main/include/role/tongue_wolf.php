<?php
/*
  ◆舌禍狼 (tongue_wolf)
  ○仕様
  ・能力結果：襲撃
  ・襲撃追加：役職鑑定
*/
RoleLoader::LoadFile('wolf');
class Role_tongue_wolf extends Role_wolf {
  public $result = RoleAbility::TONGUE_WOLF;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  protected function WolfKillAction(User $user) {
    $actor = $this->GetWolfVoter();
    if (false === $actor->IsActive()) { //能力失効判定
      return;
    }
    if ($user->IsRole('human')) { //村人なら能力失効
      $actor->LostAbility();
    }
    DB::$ROOM->StoreAbility($this->result, $user->main_role, $user->GetName(), $actor->id);
  }
}
