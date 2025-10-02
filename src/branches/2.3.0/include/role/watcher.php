<?php
/*
  ◆傍観者 (watcher)
  ○仕様
  ・投票数：0
*/
class Role_watcher extends Role {
  public function FilterVoteDo(&$count) {
    $count = 0;
  }
}
