<?php
/*
  ◆翠狼 (emerald_wolf)
  ○仕様
  ・仲間人狼襲撃：共鳴者
*/
RoleLoader::LoadFile('wolf');
class Role_emerald_wolf extends Role_wolf {
  protected function WolfEatWolfAction(User $user) {
    $role = $this->GetWolfVoter()->GetID('mind_friend');
    $this->GetWolfVoter()->AddRole($role);
    $user->AddRole($role);
  }
}
