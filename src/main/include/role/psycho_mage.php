<?php
/*
  ◆精神鑑定士 (psycho_mage)
  ○仕様
  ・占い：精神鑑定
  ・呪い：無効
*/
RoleManager::LoadFile('mage');
class Role_psycho_mage extends Role_mage {
  public $mage_failed = 'mage_failed';

  function IsCursed(User $user) { return false; }

  function GetMageResult(User $user) { return $user->DistinguishLiar(); }
}
