<?php
/*
  ◆不運 (bad_luck)
  ○仕様
  ・処刑者決定：自分
*/
RoleLoader::LoadFile('decide');
class Role_bad_luck extends Role_decide {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ACTOR;
  }
}
