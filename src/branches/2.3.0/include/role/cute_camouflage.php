<?php
/*
  ◆魔が言 (cute_camouflage)
  ○仕様
  ・発言変換：完全置換 (人狼遠吠え or サーバ設定)
*/
class Role_cute_camouflage extends Role {
  public function ConvertSay() {
    //スキップ判定
    if (! DB::$ROOM->IsDay() || DB::$ROOM->IsEvent('no_cute') || ! Lottery::Percent(10)) {
      return false;
    }

    if (RoleTalkMessage::CUTE_WOLF != '') {
      $str = RoleTalkMessage::CUTE_WOLF;
    } else {
      $str = RoleTalkMessage::WOLF_HOWL;
    }
    $this->SetStack($str, 'say');
    return true;
  }
}
