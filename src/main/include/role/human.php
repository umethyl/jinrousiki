<?php
/*
  ◆村人 (human)
  ○仕様
  ・投票数：+1 (座敷童子生存 / 天候「慈雨」)
*/
class Role_human extends Role {
  public $mix_in = ['authority'];

  protected function IgnoreFilterVoteDo() {
    $flag = $this->GetStack();
    if (is_null($flag)) {
      $role = 'brownie';
      $flag = DB::$ROOM->IsEvent($role) || DB::$USER->IsLiveRole($role);
      $this->SetStack($flag);
    }
    return false === $flag;
  }
}
