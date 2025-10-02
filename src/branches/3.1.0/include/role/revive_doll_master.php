<?php
/*
  ◆西行法師 (revive_doll_master)
  ○仕様
  ・能力結果：蘇生 (天啓封印あり)
  ・蘇生率：15 + (人形 * 10) % / 誤爆有り
  ・蘇生制限：人形
*/
RoleLoader::LoadFile('doll_master');
class Role_revive_doll_master extends Role_doll_master {
  public $mix_in = array('vote' => 'poison_cat', 'protected');

  protected function OutputAddResult() {
    $this->OutputReviveResult();
  }

  protected function GetReviveRate() {
    return 15 + ($this->CountDoll() * 10);
  }

  protected function IgnoreReviveTarget(User $user) {
    return $this->IsDoll($user);
  }
}
