<?php
/*
  ◆大天狗 (soul_tengu)
  ○仕様
  ・能力結果：神通力追加
  ・神通力：役職取得
  ・神通力対象：全て
*/
RoleLoader::LoadFile('tengu');
class Role_soul_tengu extends Role_tengu {
  protected function IgnoreResult() {
    return false;
  }

  protected function OutputAddResult() {
    RoleHTML::OutputResult(RoleAbility::TENGU);
  }

  protected function IgnoreTenguTarget(User $user) {
    return false;
  }

  protected function TenguKill(User $user) {
    $this->SaveMageResult($user, $user->main_role, RoleAbility::TENGU);
  }
}
