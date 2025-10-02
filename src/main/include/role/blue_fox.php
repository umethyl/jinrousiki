<?php
/*
  ◆蒼狐 (blue_fox)
  ○仕様
  ・人狼襲撃カウンター：はぐれ者
*/
RoleManager::LoadFile('fox');
class Role_blue_fox extends Role_fox {
  function FoxEatCounter(User $user) {
    if (! $user->IsLonely()) $user->AddRole('mind_lonely');
  }
}
