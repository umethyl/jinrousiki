<?php
/*
  ◆脱落者 (dropout)
  ○仕様
  ・処刑者決定：自分 (自身と投票先が最多得票者)
*/
RoleLoader::LoadFile('counter_decide');
class Role_dropout extends Role_counter_decide {
  protected function GetCounterDecideTarget($actor, $target) {
    return $actor;
  }
}
