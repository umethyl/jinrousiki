<?php
/*
  ◆山彦 (echo_brownie)
*/
class Role_echo_brownie extends Role {
  //反響
  function EchoSay() {
    if (! Lottery::Percent(30)) return; //確率判定
    $stack = TalkDB::GetRecent();
    //連続発言検出
    if (count($stack) < 1 || $this->IsActor(DB::$USER->ByUname($stack[0]['uname']))) return;
    $str = Lottery::Get($stack);
    RoleTalk::Save($str['sentence'], DB::$ROOM->scene);
  }
}
