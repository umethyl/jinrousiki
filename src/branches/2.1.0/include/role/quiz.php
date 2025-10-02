<?php
/*
  ◆出題者 (quiz)
  ○仕様
  ・処刑者決定：同一投票先
*/
class Role_quiz extends Role {
  public $mix_in = 'decide';

  protected function OutputResult() {
    if (DB::$ROOM->IsOptionGroup('chaos')) Image::Role()->Output('quiz_chaos');
  }

  function SetVoteDay($uname) {
    if ($this->IsRealActor()) $this->AddStack($uname);
  }

  function DecideVoteKill() { $this->DecideVoteKillSame(); }
}
