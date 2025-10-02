<?php
/*
  ◆神話マニア (mania)
  ○仕様
  ・能力結果：コピー
  ・コピー役職：メイン役職
*/
class Role_mania extends Role {
  public $action      = VoteAction::MANIA;
  public $result      = RoleAbility::MANIA;
  public $action_date = RoleActionDate::FIRST;

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::MANIA, RoleAbilityMessage::MANIA, $this->action);
  }

  //コピー処理
  final public function Copy(User $user) {
    $role = $this->GetCopyResultRole($user);
    $this->CopyAddAction($user, $role);
    $this->CopyAction($user, $role);
  }

  //コピー結果役職取得 (Mixin あり)
  protected function GetCopyResultRole(User $user) {
    return $user->IsRoleGroup('mania') ? 'human' : $this->GetCopyRole($user);
  }

  //コピー役職取得
  protected function GetCopyRole(User $user) {
    return $user->main_role;
  }

  //特殊コピー処理
  protected function CopyAddAction(User $user, $role) {}

  //コピー変化処理
  protected function CopyAction(User $user, $role) {
    $actor = $this->GetActor();
    $actor->ReplaceRole($this->role, $role);
    $actor->AddRole($this->GetCopiedRole());
    DB::$ROOM->ResultAbility($this->result, $role, $user->handle_name, $actor->id);
  }

  //コピー変化後役職取得
  protected function GetCopiedRole() {
    return 'copied';
  }
}
