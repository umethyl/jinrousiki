<?php
/*
  ◆仙狸 (revive_cat)
  ○仕様
  ・蘇生率：80% (減衰 1/4) / 誤爆有り
  ・蘇生後：蘇生回数更新
*/
RoleManager::LoadFile('poison_cat');
class Role_revive_cat extends Role_poison_cat {
  public $revive_rate = 80;

  public function GetReviveRate() {
    return ceil(parent::GetReviveRate() / pow(4, $this->GetReviveCount()));
  }

  public function ReviveAction() {
    $count = $this->GetReviveCount();
    $role  = $count > 0 ? sprintf('%s[%s]', $this->role, $count) : $this->role;
    $this->GetActor()->ReplaceRole($role, sprintf('%s[%s]', $this->role, ++$count));
  }

  private function GetReviveCount() {
    return (int)$this->GetActor()->GetMainRoleTarget();
  }
}
