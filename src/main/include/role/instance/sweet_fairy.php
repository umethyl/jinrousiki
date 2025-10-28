<?php
/*
  ◆恋妖精 (sweet_fairy)
  ○仕様
  ・悪戯：サブ役職付加 (悲恋)
*/
RoleLoader::LoadFile('fairy');
class Role_sweet_fairy extends Role_fairy {
  public $action = VoteAction::CUPID;
  public $submit = VoteAction::FAIRY;

  protected function GetActionDate() {
    return RoleActionDate::FIRST;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function SetVoteNightTargetList(array $list) {
    $stack = [];
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $this->ValidateVoteNightTarget($user, $user->IsLive());
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
  }

  public function SetVoteNightTargetListAction() {
    $list  = $this->GetStack('target_list');
    $stack = [];
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      $user->AddRole($this->GetActor()->GetID('sweet_status'));
    }
    $this->SetStack(ArrayFilter::ConcatKey($list), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($stack), 'target_handle');
    $this->SetStack(VoteAction::FAIRY, 'message'); //Talk の action は FAIRY
  }
}
