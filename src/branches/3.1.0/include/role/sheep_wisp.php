<?php
/*
  ◆羊皮 (sheep_wisp)
  ○仕様
  ・表示：当日限定
  ・占い結果：村人
*/
RoleLoader::LoadFile('wisp');
class Role_sheep_wisp extends Role_wisp {
  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  protected function IgnoreJammerMageResult(User $user) {
    return ! $user->IsDoomRole($this->role);
  }

  protected function GetWispRole($reverse) {
    return $reverse ? 'wolf' : 'human';
  }
}
