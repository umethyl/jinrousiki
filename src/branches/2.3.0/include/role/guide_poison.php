<?php
/*
  ◆誘毒者 (guide_poison)
  ○仕様
  ・毒：毒能力者
*/
RoleManager::LoadFile('poison');
class Role_guide_poison extends Role_poison {
  public function IsPoisonTarget(User $user) {
    return $user->IsRoleGroup('poison');
  }
}
