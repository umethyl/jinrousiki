<?php
/*
  ◆夢馬 (homogeneous_vampire)
  ○仕様
  ・吸血：同性以外なら性転換 + 性別鑑定
*/
RoleLoader::LoadFile('vampire');
class Role_homogeneous_vampire extends Role_vampire {
  public $result = RoleAbility::VAMPIRE;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function InfectAction(User $user) {
    if ($this->IsInfectSexExchange($user)) {
      Sex::Exchange($user);
    }
    $sex = Sex::Distinguish($user);
    DB::$ROOM->StoreAbility($this->result, $sex, $user->GetName(), $this->GetID());
  }

  //性転換対象ユーザー判定
  protected function IsInfectSexExchange(User $user) {
    return false === Sex::IsSame($user, $this->GetActor());
  }
}
