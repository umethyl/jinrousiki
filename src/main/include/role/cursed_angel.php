<?php
/*
  ◆堕天使 (cursed_angel)
  ○仕様
  ・共感者判定：別陣営
  ・ショック死：恋人からの得票
*/
RoleManager::LoadFile('angel');
class Role_cursed_angel extends Role_angel {
  public $mix_in = array('chicken');
  public $sudden_death = 'SEALED';

  protected function IsSympathy(User $a, User $b) {
    return $a->GetCamp() != $b->GetCamp();
  }

  public function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || $this->GetActor()->IsAvoidLovers(true);
  }

  public function IsSuddenDeath() {
    foreach ($this->GetVotedUname() as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsLovers()) return true;
    }
    return false;
  }
}
