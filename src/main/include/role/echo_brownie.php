<?php
/*
  ◆山彦 (echo_brownie)
*/
class Role_echo_brownie extends Role {
  //反響
  function EchoSay() {
    if (mt_rand(0, 9) > 3) return; //確率判定
    $query = 'SELECT uname, sentence FROM talk' . DB::$ROOM->GetQuery() .
      ' AND scene = "' . DB::$ROOM->scene . '" ORDER BY id DESC LIMIT 5';
    $stack = DB::FetchAssoc($query);
    if (count($stack) < 1 || $this->IsActor($stack[0]['uname'])) return; //連続発言検出
    $str = Lottery::Get($stack);
    RoleTalk::Save($str['sentence'], DB::$ROOM->scene);
  }
}
