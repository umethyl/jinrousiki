<?php
/*
  ◆山彦 (echo_brownie)
*/
class Role_echo_brownie extends Role {
  //反響
  public function EchoSay() {
    if (! Lottery::Percent(30)) { //確率判定
      return;
    }

    $stack = TalkDB::GetRecent();
    //連続発言検出
    if (count($stack) < 1 || $this->IsActor(DB::$USER->ByUname($stack[0]['uname']))) {
      return;
    }

    $str = Lottery::Get($stack);
    RoleTalk::Store(new RoleTalkStruct($str['sentence']));
  }
}
