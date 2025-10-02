<?php
/*
  ◆百合天使 (lily_angel)
  ○仕様
  ・共感者判定：両方女性
*/
RoleManager::LoadFile('angel');
class Role_lily_angel extends Role_angel {
  protected function IsSympathy(User $a, User $b) { return $a->IsFemale() && $b->IsFemale(); }
}
