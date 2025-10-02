<?php
/*
  ◆ブン屋 (reporter)
  ○仕様
  ・能力結果：尾行(襲撃情報取得)
*/
class Role_reporter extends Role {
  public $action      = VoteAction::REPORTER;
  public $result      = RoleAbility::REPORTER;
  public $action_date = RoleActionDate::AFTER;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::GUARD, RoleAbilityMessage::REPORTER, $this->action);
  }

  //尾行
  public function Report(User $user) {
    $target = $this->GetWolfTarget();
    if ($user->IsSame($target)) { //尾行成功
      if (! $user->wolf_eat) return; //人狼襲撃が失敗していたらスキップ
      $result = $this->GetWolfVoter()->GetName();
      DB::$ROOM->ResultAbility($this->result, $result, $target->GetName(), $this->GetID());
    } elseif ($user->IsLiveRoleGroup('wolf', 'fox')) { //尾行対象が人狼か妖狐なら死亡する
      DB::$USER->Kill($this->GetID(), DeadReason::REPORTER_DUTY);
    }
  }
}
