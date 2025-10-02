<?php
/*
  ◆ブン屋 (reporter)
  ○仕様
  ・尾行：襲撃情報取得
*/
class Role_reporter extends Role {
  public $action = 'REPORTER_DO';
  public $result = 'REPORTER_SUCCESS';
  public $ignore_message = '初日の尾行はできません';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult($this->result);
  }

  function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'reporter_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  //尾行
  function Report(User $user) {
    $target = $this->GetWolfTarget();
    if ($user->IsSame($target->uname)) { //尾行成功
      if (! $user->wolf_eat) return; //人狼襲撃が失敗していたらスキップ
      $result = DB::$USER->GetHandleName($this->GetWolfVoter()->uname, true);
      $name   = DB::$USER->GetHandleName($target->uname, true);
      DB::$ROOM->ResultAbility($this->result, $result, $name, $this->GetID());
    }
    elseif ($user->IsLiveRoleGroup('wolf', 'fox')) { //尾行対象が人狼か妖狐なら殺される
      DB::$USER->Kill($this->GetID(), 'REPORTER_DUTY');
    }
  }
}
