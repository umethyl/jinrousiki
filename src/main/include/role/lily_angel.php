<?php
/*
  ◆百合天使 (lily_angel)
  ○仕様
  ・共感者判定：両方女性
*/
RoleLoader::LoadFile('angel');
class Role_lily_angel extends Role_angel {
  protected function IsSympathy(User $a, User $b) {
    return Sex::IsFemale($a) && Sex::IsFemale($b);
  }
}
