<?php
/*
  ◆独身貴族 (celibacy)
  ○仕様
  ・ショック死：恋人からの得票
*/
RoleLoader::LoadFile('chicken');
class Role_celibacy extends Role_chicken {
  protected function IsSuddenDeath() {
    foreach ($this->GetVotedUname() as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsRole('lovers')) return true;
    }
    return false;
  }

  protected function GetSuddenDeathType() {
    return 'CELIBACY';
  }
}
