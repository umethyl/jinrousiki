<?php
/*
  ◆木っ端天狗 (eclipse_tengu)
  ○仕様
  ・神通力：天狗倒し (確率自己反射)
*/
RoleManager::LoadFile('tengu');
class Role_eclipse_tengu extends Role_tengu {
  protected function TenguKill(User $user) {
    $target = Lottery::Bool() ? $user : $this->GetActor();
    $target->AddRole('tengu_voice');
  }
}
