<?php
/*
  ◆牡丹灯籠 (passion_vampire)
  ○仕様
  ・吸血：恋色迷彩 (一定確率)
*/
RoleManager::LoadFile('vampire');
class Role_passion_vampire extends Role_vampire {
  protected function InfectAction(User $user) {
    if (Lottery::Bool()) $user->AddRole('passion');
  }
}
