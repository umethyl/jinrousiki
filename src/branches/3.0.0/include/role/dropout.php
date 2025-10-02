<?php
/*
  ◆脱落者 (dropout)
  ○仕様
  ・処刑者決定：自分 (自身と投票先が最多得票者)
*/
RoleManager::LoadFile('counter_decide');
class Role_dropout extends Role_counter_decide {
  public $decide_target = 'actor';
}
