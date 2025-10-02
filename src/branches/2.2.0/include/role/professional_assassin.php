<?php
/*
  ◆仕事人 (professional_assassin)
  ○仕様
  ・暗殺失敗：村人陣営 + 人外カウント
*/
RoleManager::LoadFile('assassin');
class Role_professional_assassin extends Role_assassin {
  protected function IgnoreAssassin(User $user) {
    return $user->IsCamp('human', true) || $user->IsInhuman();
  }
}
