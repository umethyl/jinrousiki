<?php
/*
  ◆出題者 (quiz)
  ○仕様
  ・能力結果：闇鍋モード専用メッセージ
  ・処刑者決定：同一投票先
*/
class Role_quiz extends Role {
  public $mix_in = array('decide');

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsOptionGroup('chaos');
  }

  protected function OutputAddResult() {
    ImageManager::Role()->Output('quiz_chaos');
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function DecideVoteKill() {
    $this->DecideVoteKillSame();
  }
}
