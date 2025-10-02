<?php
/*
  ◆さとり (mind_scanner)
  ○仕様
  ・追加役職：サトラレ
  ・投票結果：なし
  ・投票：1日目のみ
*/
class Role_mind_scanner extends Role {
  public $action = 'MIND_SCANNER_DO';
  public $mind_role = 'mind_read';

  protected function OutputPartner() {
    if (DB::$ROOM->date < 2 || is_null($this->mind_role)) return;
    $id = $this->GetID();
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->mind_role, $id)) $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, 'mind_scanner_target');
  }

  function OutputAction() {
    RoleHTML::OutputVote('mind-scanner-do', 'mind_scanner_do', $this->action);
  }

  function IsVote() { return parent::IsVote() && DB::$ROOM->IsDate(1); }

  function GetIgnoreMessage() { return '初日以外は投票できません'; }

  function IsVoteCheckbox(User $user, $live) {
    return parent::IsVoteCheckbox($user, $live) && ! $user->IsDummyBoy();
  }

  function IgnoreVoteNight(User $user, $live) {
    if (! is_null($str = parent::IgnoreVoteNight($user, $live))) return $str;
    return $user->IsDummyBoy() ? '身代わり君には投票できません' : null;
  }

  //透視
  function MindScan(User $user) { $user->AddRole($this->GetActor()->GetID($this->mind_role)); }
}
