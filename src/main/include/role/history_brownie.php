<?php
/*
  ◆白澤 (history_brownie)
  ○仕様
  ・人狼襲撃：特殊イベント (夜スキップ)
*/
class Role_history_brownie extends Role {
  public function WolfEatCounter(User $user) {
    $this->AddStack($this->GetWolfEatCounterEvent(), 'event');
  }

  //人狼襲撃トリガーイベント取得
  protected function GetWolfEatCounterEvent() {
    return 'skip_night';
  }
}
