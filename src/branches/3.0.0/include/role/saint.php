<?php
/*
  ◆聖女 (saint)
  ○仕様
  ・役職表示：村人
  ・処刑者決定：候補者の内訳によって変化
*/
class Role_saint extends Role {
  public $mix_in = array('decide');
  public $display_role  = 'human';
  public $vote_day_type = 'target';

  public function DecideVoteKill() {
    if ($this->IsVoteKill()) return;

    $self   = array();
    $target = array();
    foreach ($this->GetVotePossible() as $uname) {
      $user = DB::$USER->ByRealUname($uname); //$uname は仮想ユーザ
      if ($user->IsRole('saint')) {
	$self[] = $uname;
      }
      if (! $user->IsCamp('human', true)) {
	$target[] = $uname;
      }
    }

    if (count($self) > 0 && count($target) < 2) { //対象を一人に固定できる時のみ有効
      if (count($target) == 1) {
	$this->SetVoteKill(array_shift($target));
      }
      elseif (count($self) == 1) {
	$this->SetVoteKill(array_shift($self));
      }
    }
  }
}
