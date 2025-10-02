<?php
/*
  ◆出題者 (quiz)
  ○仕様
  ・処刑者決定：同一投票先
*/
class Role_quiz extends Role {
  public $mix_in = 'decide';
  public $vote_day_type = 'real';

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsOptionGroup('chaos');
  }

  protected function OutputAddResult() {
    Image::Role()->Output('quiz_chaos');
  }

  public function DecideVoteKill() {
    $this->DecideVoteKillSame();
  }
}
