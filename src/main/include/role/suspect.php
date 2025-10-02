<?php
/*
  ◆不審者 (suspect)
  ○仕様
  ・役職表示：村人
  ・発言変換：完全置換 (人狼遠吠え or サーバ設定)
*/
class Role_suspect extends Role {
  public $display_role = 'human';

  function ConvertSay() {
    if (! DB::$ROOM->IsDay()) return false; //スキップ判定

    $rate = GameConfig::CUTE_WOLF_RATE * (DB::$ROOM->IsEvent('boost_cute') ? 5 : 1);
    //Text::p($rate, $this->role);
    if (mt_rand(1, 100) > $rate) return false;

    $this->SetStack(Message::$cute_wolf != '' ? Message::$cute_wolf : Message::$wolf_howl, 'say');
    return true;
  }
}
