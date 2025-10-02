<?php
/*
  ◆西行法師 (revive_doll_master)
  ○仕様
  ・蘇生率：15 + (人形 * 10) % / 誤爆有り
  ・蘇生制限：人形
*/
RoleManager::LoadFile('doll_master');
class Role_revive_doll_master extends Role_doll_master {
  public $mix_in = array('vote' => 'poison_cat', 'protected');

  protected function OutputAddResult() {
    $this->OutputReviveResult();
  }

  public function GetReviveRate() {
    return 15 + $this->GetDollCount() * 10;
  }

  public function IgnoreReviveTarget(User $user) {
    return $this->IsDoll($user);
  }
}
