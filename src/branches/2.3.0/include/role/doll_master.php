<?php
/*
  ◆人形遣い (doll_master)
  ○仕様
  ・勝利：通常
  ・仲間表示：なし
  ・身代わり：人形
*/
RoleManager::LoadFile('doll');
class Role_doll_master extends Role_doll {
  public $mix_in = 'protected';

  protected function OutputPartner() {
    return;
  }

  public function Win($winner) {
    return true;
  }

  public function IsSacrifice(User $user) {
    return $this->IsDoll($user);
  }
}
