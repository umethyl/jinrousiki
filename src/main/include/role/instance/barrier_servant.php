<?php
/*
  ◆番犬 (barrier_servant)
  ○仕様
  ・主支援役職：従者護衛
  ・主裏切り：主襲撃
  ・従者護衛(人狼襲撃耐性)：無効(生存＆支援中)
*/
RoleLoader::LoadFile('servant');
class Role_barrier_servant extends RoleAbility_servant {
  protected function GetServantTargetRole() {
    return $this->GetActor()->GetID('serve_protect');
  }

  protected function ServantEndAction(User $user) {
    if (true !== RoleUser::Avoid($user, true)) {
      $this->AddStack($user->id, 'servant_kill');
    }
  }

  public function ResistWolfEat() {
    //生存時のみ有効
    $user = $this->GetActor();
    if ($user->IsDead()) {
      return false;
    }

    //支援中判定
    return $user->IsActive();
  }
}
