<?php
/*
  ◆仕事人 (professional_assassin)
  ○仕様
  ・暗殺：非村人陣営 + 非人外限定
*/
RoleManager::LoadFile('assassin');
class Role_professional_assassin extends Role_assassin {
  function Assassin(User $user) {
    if ($user->IsCamp('human', true) || $user->IsWolf() || $user->IsFox()) return false;
    return parent::Assassin($user);
  }
}
