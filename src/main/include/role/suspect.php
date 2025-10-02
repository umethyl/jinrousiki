<?php
/*
  ◆不審者 (suspect)
  ○仕様
  ・役職表示：村人
  ・発言変換：完全置換 (人狼遠吠え or サーバ設定)
*/
class Role_suspect extends Role {
  public $display_role = 'human';

  public function ConvertSay() {
    if (! DB::$ROOM->IsDay() || DB::$ROOM->IsEvent('no_cute')) return false; //スキップ判定

    $rate = GameConfig::CUTE_WOLF_RATE * (DB::$ROOM->IsEvent('boost_cute') ? 5 : 1);
    //Text::p($rate, "◆Rate [{$this->role}]");
    if (! Lottery::Percent($rate)) return false;

    if (RoleTalkMessage::CUTE_WOLF != '') {
      $str = RoleTalkMessage::CUTE_WOLF;
    } else {
      $str = RoleTalkMessage::WOLF_HOWL;
    }
    $this->SetStack($str, 'say');
    return true;
  }
}
