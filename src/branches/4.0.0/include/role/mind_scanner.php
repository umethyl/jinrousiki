<?php
/*
  ◆さとり (mind_scanner)
  ○仕様
  ・追加役職：サトラレ
  ・仲間表示：対象者 (憑依追跡なし)
  ・投票結果：なし
  ・投票：1 日目のみ
*/
class Role_mind_scanner extends Role {
  public $action      = VoteAction::SCAN;
  public $action_date = RoleActionDate::FIRST;

  protected function IgnorePartner() {
    return DB::$ROOM->date < 2 || is_null($this->GetMindRole());
  }

  //透視対象役職取得
  protected function GetMindRole() {
    return 'mind_read';
  }

  protected function GetPartner() {
    $id    = $this->GetID();
    $role  = $this->GetMindRole();
    $stack = [];
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	$stack[] = $user->handle_name; //憑依追跡なし
      }
    }
    return ['mind_scanner_target' => $stack];
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::SCAN, RoleAbilityMessage::SCAN, $this->action);
  }

  protected function IgnoreVoteCheckboxDummyBoy() {
    return true;
  }

  //透視
  public function MindScan(User $user) {
    $user->AddRole($this->GetActor()->GetID($this->GetMindRole()));
  }
}
