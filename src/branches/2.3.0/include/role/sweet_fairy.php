<?php
/*
  ◆恋妖精 (sweet_fairy)
  ○仕様
  ・悪戯：悲恋 (sweet_status)
*/
RoleManager::LoadFile('fairy');
class Role_sweet_fairy extends Role_fairy {
  public $action = 'CUPID_DO';
  public $submit = 'fairy_do';

  public function IsVote() {
    return DB::$ROOM->IsDate(1);
  }

  protected function GetIgnoreMessage() {
    return VoteRoleMessage::POSSIBLE_ONLY_FIRST_DAY;
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $live && ! $user->IsDummyBoy();
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  public function SetVoteNightUserList(array $list) {
    $stack = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if ($user->IsDead())     return VoteRoleMessage::TARGET_DEAD;
      if ($user->IsDummyBoy()) return VoteRoleMessage::TARGET_DUMMY_BOY;
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
    return null;
  }

  public function VoteNightAction() {
    $list  = $this->GetStack('target_list');
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      $user->AddRole($this->GetActor()->GetID('sweet_status'));
    }
    $this->SetStack(implode(' ', array_keys($list)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
    $this->SetStack('FAIRY_DO', 'message'); //Talk の action は FAIRY_DO
  }
}
