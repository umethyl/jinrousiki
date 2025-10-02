<?php
/*
  ◆剣闘士 (critical_duelist)
  ○仕様
  ・自分撃ち：固定
  ・投票数：+100 (5%)
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_critical_duelist extends Role_valkyrja_duelist {
  public $mix_in = ['critical_voter'];

  protected function IgnoreFilterVoteDo() {
    return false === Lottery::Percent(5);
  }

  protected function FixSelfShoot() {
    return true;
  }
}
