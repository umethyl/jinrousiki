<?php
/*
  ◆辻斬り (soul_assassin)
  ○仕様
  ・能力結果：暗殺
  ・暗殺：役職判定 / 毒死(毒能力者)
*/
RoleLoader::LoadFile('assassin');
class Role_soul_assassin extends Role_assassin {
  public $result = RoleAbility::ASSASSIN;

  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  protected function AssassinAction(User $user) {
    if (RoleUser::IsPoison($user)) {
      DB::$USER->Kill($this->GetID(), DeadReason::POISON_DEAD);
    } else {
      DB::$ROOM->StoreAbility($this->result, $user->main_role, $user->GetName(), $this->GetID());
    }
  }
}
