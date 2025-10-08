<?php
/*
  ◆墓守 (grave_guard)
  ○仕様
  ・護衛失敗：70% / 生存者限定
  ・狩り：死者 + 人外カウント (死の宣告)
*/
RoleLoader::LoadFile('guard');
class Role_grave_guard extends Role_guard {
  protected function FixLiveVoteNightIconPath() {
    return true;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  public function GuardFailed(User $user) {
    return DB::$USER->IsVirtualLive($user->id) && Lottery::Percent(70);
  }

  protected function IsHunt(User $user) {
    return $user->IsDead() && RoleUser::IsInhuman($user);
  }

  protected function HuntKill(User $user) {
    return $user->AddDoom(3);
  }
}
