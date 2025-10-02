<?php
/*
  ◆天邪鬼 (perverseness)
  ○仕様
  ・ショック死：自分の投票先に複数の人が投票している
*/
RoleLoader::LoadFile('chicken');
class Role_perverseness extends Role_chicken {
  protected function IsSuddenDeath() {
    return $this->CountVoteTarget() > 1;
  }

  protected function GetSuddenDeathType() {
    return 'PERVERSENESS';
  }
}
