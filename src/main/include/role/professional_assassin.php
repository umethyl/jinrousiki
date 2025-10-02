<?php
/*
  ◆仕事人 (professional_assassin)
  ○仕様
  ・暗殺失敗：村人陣営 or 人外カウント
*/
RoleLoader::LoadFile('assassin');
class Role_professional_assassin extends Role_assassin {
  protected function IgnoreAssassin(User $user) {
    return $user->IsWinCamp(Camp::HUMAN) || RoleUser::IsInhuman($user);
  }
}
