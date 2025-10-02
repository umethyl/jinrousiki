<?php
/*
  ◆織姫 (vega_lovers)
  ○仕様
  ・表示：2 日目以降
  ・投票数：0
  ・得票数：0
  ・処刑者決定：決定者相当 (優先順位低め)
  ・人狼襲撃耐性：無効
*/
class Role_vega_lovers extends Role {
  public $mix_in = array('decide', 'watcher', 'upper_luck');
  public $vote_day_type = 'target';

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function GetVotePollCount() {
    return 0;
  }

  public function IsUpdateFilterVotePoll() {
    return true;
  }

  public function WolfEatResist() {
    return true;
  }
}
