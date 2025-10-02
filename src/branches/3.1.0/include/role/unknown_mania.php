<?php
/*
  ◆鵺 (unknown_mania)
  ○仕様
  ・追加役職：なし
*/
RoleLoader::LoadFile('mania');
class Role_unknown_mania extends Role_mania {
  protected function IgnoreVoteCheckboxDummyBoy() {
    return true;
  }

  protected function GetCopyResultRole(User $user) {
    return $this->GetCopyRole($this->GetActor());
  }

  protected function GetCopyRole(User $user) {
    return null;
  }

  protected function CopyAddAction(User $user, $role) {
    $user->AddRole(Text::AddFooter($this->GetCopiedRole(), $role, ' '));
    $this->CopySelfAction($user);
  }

  protected function CopyAction(User $user, $role) {
    $actor = $this->GetActor();
    $actor->AddMainRole($user->id);
    $actor->AddRole($this->GetCopiedRole());
  }

  protected function GetCopiedRole() {
    return $this->GetActor()->GetID('mind_friend');
  }

  //特殊コピー処理 (本人用)
  protected function CopySelfAction(User $user) {}
}
