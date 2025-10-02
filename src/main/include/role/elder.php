<?php
/*
  ◆長老 (elder)
  ○仕様
  ・投票数：+1 (3% で +100)
*/
class Role_elder extends Role {
  public $mix_in = array('authority');

  public function GetVoteDoCount() {
    return Lottery::Percent(3) ? 100 : 1;
  }
}
