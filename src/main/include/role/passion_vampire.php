<?php
/*
  ◆牡丹灯籠 (passion_vampire)
  ○仕様
  ・吸血：恋色迷彩 (一定確率)
*/
RoleManager::LoadFile('vampire');
class Role_passion_vampire extends Role_vampire {
  function Infect(User $user) {
    parent::Infect($user);
    if (mt_rand(0, 1) > 0) $user->AddRole('passion');
  }
}
