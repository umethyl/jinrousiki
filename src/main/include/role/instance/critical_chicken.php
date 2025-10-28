<?php
/*
  ◆魔女の一撃 (critical_chicken)
  ○仕様
  ・ショック死：得票(100票以上)
*/
RoleLoader::LoadFile('chicken');
class Role_critical_chicken extends Role_chicken {
  protected function IsSuddenDeath() {
    $stack = RoleManager::Stack()->Get(VoteDayElement::COUNT_LIST);
    return $stack[$this->GetActor()->uname] >= 100;
  }

  protected function GetSuddenDeathType() {
    return 'CRITICAL_CHICKEN';
  }
}
