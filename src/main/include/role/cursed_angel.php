<?php
/*
  ◆堕天使 (cursed_angel)
  ○仕様
  ・共感者判定：別陣営
  ・ショック死：恋人からの得票
*/
RoleLoader::LoadFile('angel');
class Role_cursed_angel extends Role_angel {
  public $mix_in = array('chicken');

  protected function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || RoleUser::IsAvoidLovers($this->GetActor(), true);
  }

  protected function IsSuddenDeath() {
    foreach ($this->GetVotedUname() as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsRole('lovers')) return true;
    }
    return false;
  }

  protected function GetSuddenDeathType() {
    return 'SEALED';
  }

  protected function IsSympathy(User $a, User $b) {
    return $a->GetMainCamp(true) != $b->GetMainCamp(true); //神話マニア陣営を区別する
  }
}
