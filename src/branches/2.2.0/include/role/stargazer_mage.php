<?php
/*
  ◆占星術師 (stargazer_mage)
  ○仕様
  ・占い：投票能力鑑定
*/
RoleManager::LoadFile('psycho_mage');
class Role_stargazer_mage extends Role_psycho_mage {
  function GetMageResult(User $user) { return $this->Stargazer($user); }

  //投票能力鑑定
  final function Stargazer(User $user) {
    return (array_key_exists($user->id, DB::$ROOM->vote) || $user->IsWolf()) ?
      'stargazer_mage_ability' : 'stargazer_mage_nothing';
  }
}
