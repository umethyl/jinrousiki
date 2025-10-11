<?php
/*
  ◆夜雀 (blind_guard)
  ○仕様
  ・護衛制限：なし
  ・護衛処理：目隠し
  ・狩り：なし
*/
RoleLoader::LoadFile('guard');
class Role_blind_guard extends Role_guard {
  public function UnlimitedGuard() {
    return true;
  }

  public function GuardAction(User $user) {
    $this->GetVoter()->AddRole('blinder');
  }

  public function IgnoreHunt() {
    return true;
  }
}
