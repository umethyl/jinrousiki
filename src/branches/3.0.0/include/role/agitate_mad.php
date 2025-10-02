<?php
/*
  ◆扇動者 (agitate_mad)
  ○仕様
  ・処刑者決定：同一投票先 + 残りをまとめてショック死
*/
class Role_agitate_mad extends Role {
  public $mix_in = array('decide');
  public $vote_day_type = 'stack';
  public $sudden_death  = 'AGITATED';

  public function DecideVoteKill() {
    if ($this->DecideVoteKillSame()) return;

    $uname = $this->GetVoteKill();
    foreach ($this->GetStack('max_voted') as $target_uname) { //$target_uname は仮想ユーザ
      if ($target_uname != $uname) {
	$this->SuddenDeathKill(DB::$USER->ByRealUname($target_uname)->id);
      }
    }
  }
}
