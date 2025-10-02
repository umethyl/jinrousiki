<?php
/*
  ◆疫病神 (plague)
  ○仕様
  ・処刑者決定：除外 (自分の投票先)
*/
RoleLoader::LoadFile('good_luck');
class Role_plague extends Role_good_luck {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::TARGET;
  }
}
