<?php
/*
  ◆八咫烏 (sun_brownie)
  ○仕様
  ・処刑：特殊イベント (目隠し)
  ・人狼襲撃：特殊イベント (公開者)
*/
RoleManager::LoadFile('history_brownie');
class Role_sun_brownie extends Role_history_brownie {
  public $event_day   = 'blinder';
  public $event_night = 'mind_open';

  public function VoteKillCounter(array $list) {
    DB::$ROOM->SystemMessage($this->event_day, 'EVENT', 1);
  }
}
