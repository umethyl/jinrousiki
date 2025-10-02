<?php
/*
  ◆恋妖精 (sweet_fairy)
  ○仕様
  ・悪戯：サブ役職付加 (悲恋)
*/
RoleLoader::LoadFile('fairy');
class Role_sweet_fairy extends Role_fairy {
  public $action      = VoteAction::CUPID;
  public $action_date = RoleActionDate::FIRST;
  public $submit      = VoteAction::FAIRY;

  protected function IgnoreVoteCheckboxSelf() {
    return false;
  }

  protected function IgnoreVoteCheckboxDummyBoy() {
    return true;
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function SetVoteNightTargetList(array $list) {
    $stack = [];
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $str  = $this->ValidateVoteNightTarget($user, $user->IsLive());
      if (! is_null($str)) return $str;
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
    return null;
  }

  public function VoteNightAction() {
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
