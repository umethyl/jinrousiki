<?php
/*
  ◆疫病神 (plague)
  ○仕様
  ・処刑者決定：除外 (自分の投票先)
*/
RoleManager::LoadFile('good_luck');
class Role_plague extends Role_good_luck {
  public $vote_day_type = 'target';
}
