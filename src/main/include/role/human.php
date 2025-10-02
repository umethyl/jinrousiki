<?php
/*
  ◆村人 (human)
  ○仕様
  ・投票数：+1 (座敷童子生存 / 天候「疎雨」)
*/
class Role_human extends Role {
  public $mix_in = array('authority');

  public function IgnoreFilterVoteDo() {
    if (is_null($flag = $this->GetStack())) {
      $role = 'brownie';
      $flag = DB::$ROOM->IsEvent($role) || DB::$USER->IsLiveRole($role);
      $this->SetStack($flag);
    }
    return ! $flag;
  }
}
