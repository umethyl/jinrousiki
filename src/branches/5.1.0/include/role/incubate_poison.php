<?php
/*
  ◆潜毒者 (incubate_poison)
  ○仕様
  ・能力結果：能力発現
  ・毒：5日目以降 / 人外カウント
*/
RoleLoader::LoadFile('poison');
class Role_incubate_poison extends Role_poison {
  protected function IgnoreResult() {
    return false === $this->IsPoison();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_poison', null);
  }

  public function IsPoison() {
    return DB::$ROOM->date > 4;
  }

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsInhuman($user);
  }
}
