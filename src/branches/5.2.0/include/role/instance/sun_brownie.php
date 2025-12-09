<?php
/*
  ◆八咫烏 (sun_brownie)
  ○仕様
  ・処刑：特殊イベント (目隠し)
  ・人狼襲撃：特殊イベント (公開者)
*/
RoleLoader::LoadFile('history_brownie');
class Role_sun_brownie extends Role_history_brownie {
  public function VoteKillCounter(array $list) {
    DB::$ROOM->StoreEvent('blinder', EventType::EVENT, 1);
  }

  protected function GetWolfEatCounterEvent() {
    return 'mind_open';
  }
}
