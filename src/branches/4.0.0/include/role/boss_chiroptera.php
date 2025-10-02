<?php
/*
  ◆大蝙蝠 (boss_chiroptera)
  ○仕様
  ・身代わり：蝙蝠陣営
*/
class Role_boss_chiroptera extends Role {
  public $mix_in = ['protected'];

  protected function IsSacrifice(User $user) {
    return ! $this->IsActor($user) && $user->IsMainCamp(Camp::CHIROPTERA);
  }
}
