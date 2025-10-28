<?php
/*
  ◆狛犬 (finder_guard)
  ○仕様
  ・護衛失敗：成功率 45% or 90%
  ・護衛制限：なし
  ・狩り：覚醒能力者判定
*/
RoleLoader::LoadFile('guard');
class Role_finder_guard extends Role_guard {
  public function GuardFailed(User $user) {
    $rate = $this->IsGuardFindTarget($user) ? 90 : 45;
    return false === Lottery::Percent($rate);
  }

  //捜索対象役職判定
  private function IsGuardFindTarget(User $user) {
    if (DB::$ROOM->IsEvent('no_hunt')) { //川霧なら無効
      return false;
    }
    return $user->IsRole(RoleFilterData::$soul_delay_copy);
  }

  public function UnlimitedGuard() {
    return true;
  }

  protected function IsHunt(User $user) {
    return $this->IsGuardFindTarget($user);
  }

  protected function HuntKill(User $user) {
    DB::$ROOM->StoreAbility(RoleAbility::HUNTED, 'found', $user->GetName(), $this->GetID());
  }
}
