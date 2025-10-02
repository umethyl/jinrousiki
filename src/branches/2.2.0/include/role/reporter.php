<?php
/*
  ◆ブン屋 (reporter)
  ○仕様
  ・尾行：襲撃情報取得
*/
class Role_reporter extends Role {
  public $action = 'REPORTER_DO';
  public $result = 'REPORTER_SUCCESS';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult($this->result);
  }

  function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'reporter_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日の尾行はできません'; }

  //尾行
  function Report(User $user) {
    $target = $this->GetWolfTarget();
    if ($user->IsSame($target)) { //尾行成功
      if (! $user->wolf_eat) return; //人狼襲撃が失敗していたらスキップ
      $result = $this->GetWolfVoter()->GetName();
      DB::$ROOM->ResultAbility($this->result, $result, $target->GetName(), $this->GetID());
    }
    elseif ($user->IsLiveRoleGroup('wolf', 'fox')) { //尾行対象が人狼か妖狐なら死亡する
      DB::$USER->Kill($this->GetID(), 'REPORTER_DUTY');
    }
  }
}
