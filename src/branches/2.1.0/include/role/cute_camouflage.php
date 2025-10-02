<?php
/*
  ◆魔が言 (cute_camouflage)
  ○仕様
  ・発言変換：完全置換 (人狼遠吠え or サーバ設定)
*/
class Role_cute_camouflage extends Role {
  function ConvertSay() {
    if (! DB::$ROOM->IsDay() || mt_rand(0, 9) > 0) return false; //スキップ判定
    $this->SetStack(Message::$cute_wolf != '' ? Message::$cute_wolf : Message::$wolf_howl, 'say');
    return true;
  }
}
