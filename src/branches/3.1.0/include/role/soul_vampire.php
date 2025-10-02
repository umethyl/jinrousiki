<?php
/*
  ◆吸血姫 (soul_vampire)
  ○仕様
  ・能力結果：吸血
  ・対吸血：反射
  ・吸血：役職取得
*/
RoleLoader::LoadFile('vampire');
class Role_soul_vampire extends Role_vampire {
  public $result = RoleAbility::VAMPIRE;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function InfectVampire(User $user) {
    if (! RoleUser::IsAvoid($user)) {
      $this->AddSuccess($user->id, RoleVoteSuccess::VAMPIRE_KILL);
    }
  }

  protected function InfectAction(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->GetName(), $this->GetID());
  }
}
