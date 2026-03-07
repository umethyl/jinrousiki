<?php
/*
  ◆桃娘 (scarlet_peach)
  ○仕様
  ・役職表示：村人
  ・処刑：専用メッセージ出力
  ・人狼襲撃：専用メッセージ出力
*/
class Role_scarlet_peach extends Role {
  public $display_role = 'human';

  public function VoteKillCounter(array $list) {
    $this->StorePeachDead();
  }

  public function WolfEatCounter(User $user) {
    $this->StorePeachDead();
  }

  //桃娘死亡メッセージ出力
  private function StorePeachDead() {
    return DB::$ROOM->StoreDead(null, DeadReason::PEACH_DEAD);
  }
}
