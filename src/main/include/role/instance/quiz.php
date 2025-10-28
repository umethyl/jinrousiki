<?php
/*
  ◆出題者 (quiz)
  ○仕様
  ・能力結果：闇鍋モード専用メッセージ
  ・処刑自己投票：有効
  ・処刑者決定：同一投票先
  ・自己処刑：降参
*/
class Role_quiz extends Role {
  public $mix_in = ['decide'];

  protected function IgnoreResult() {
    return false === OptionManager::ExistsChaos();
  }

  protected function OutputAddResult() {
    ImageManager::Role()->Output('quiz_chaos');
  }

  public function IsVoteDayCheckBoxSelf() {
    return DB::$ROOM->IsQuiz();
  }

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function DecideVoteKill() {
    $this->DecideVoteKillSame();
  }

  public function VoteKillSelfAction() {
    if (true === $this->IsVoteDayCheckBoxSelf()) {
      $this->GetActor()->Fold();
    }
  }
}
