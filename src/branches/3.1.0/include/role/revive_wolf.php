<?php
/*
  ◆仙狼 (revive_wolf)
  ○仕様
  ・復活：夜に死亡 + 能力が有効な場合のみ
*/
RoleLoader::LoadFile('wolf');
class Role_revive_wolf extends Role_wolf {
  public function Resurrect() {
    $user = $this->GetActor();
    if ($user->IsRole('lovers')) return; //無効判定 (恋人)

    if ($user->IsActive() && $user->IsLive() && $user->IsDead(true)) {
      $user->Revive();
      $user->LostAbility();
    }
  }
}
