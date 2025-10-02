<?php
/*
  ◆琴古主 (harp_mania)
  ○仕様
  ・足音：コピー先横軸
*/
RoleManager::LoadFile('lute_mania');
class Role_harp_mania extends Role_lute_mania {
  protected function GetChainStep($id) {
    $stack = array();
    $start = $id - ($id % 5 == 0 ? 5 : $id % 5) + 1;
    $max   = DB::$USER->GetUserCount();
    for ($i = $start; $i < $start + 5; $i++) {
      if ($i > $max) break;
      $stack[] = $i;
    }
    return $stack;
  }
}
