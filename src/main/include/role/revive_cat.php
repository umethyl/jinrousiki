<?php
/*
  ◆仙狸 (revive_cat)
  ○仕様
  ・蘇生率：80% (減衰 1/4) / 誤爆有り
  ・蘇生後：蘇生回数更新
*/
RoleLoader::LoadFile('poison_cat');
class Role_revive_cat extends Role_poison_cat {
  protected function GetReviveRate() {
    return ceil(80 / pow(4, $this->CountRevive()));
  }

  //蘇生回数取得
  private function CountRevive() {
    return (int)$this->GetActor()->GetMainRoleTarget();
  }

  protected function ReviveAction() {
    $count = $this->CountRevive();
    $role  = $count > 0 ? sprintf('%s[%s]', $this->role, $count) : $this->role;
    $this->GetActor()->ReplaceRole($role, sprintf('%s[%s]', $this->role, ++$count));
  }
}
