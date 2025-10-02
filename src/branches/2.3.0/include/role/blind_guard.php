<?php
/*
  ◆夜雀 (blind_guard)
  ○仕様
  ・護衛失敗：制限なし
  ・護衛処理：目隠し
  ・狩り：なし
*/
RoleManager::LoadFile('guard');
class Role_blind_guard extends Role_guard {
  public $hunt = false;

  public function IgnoreGuard() {
    return null;
  }

  public function GuardAction() {
    $this->GetVoter()->AddRole('blinder');
  }
}
