<?php
/*
  ◆ゴマすり (flattery)
  ○仕様
  ・ショック死：自分の投票先に他の人が投票していない
*/
RoleLoader::LoadFile('chicken');
class Role_flattery extends Role_chicken {
  protected function IsSuddenDeath() {
    return $this->CountVoteTarget() < 2;
  }

  protected function GetSuddenDeathType() {
    return 'FLATTERY';
  }
}
