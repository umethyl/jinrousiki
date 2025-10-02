<?php
/*
  ◆剣闘士 (critical_duelist)
  ○仕様
  ・投票数：+100 (5%)
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_critical_duelist extends Role_valkyrja_duelist {
  public $mix_in = array('critical_voter');
  public $self_shoot = true;

  public function IgnoreFilterVoteDo() {
    return ! Lottery::Percent(5);
  }
}
