<?php
/*
  ◆薔薇天使 (rose_angel)
  ○仕様
  ・共感者判定：両方男性
*/
RoleManager::LoadFile('angel');
class Role_rose_angel extends Role_angel {
  protected function IsSympathy(User $a, User $b) { return $a->IsMale() && $b->IsMale(); }
}
