<?php
/*
  ◆傘化け (amaze_mad)
  ○仕様
  ・処刑投票：特殊イベント (投票結果隠蔽)
*/
RoleLoader::LoadFile('critical_mad');
class Role_amaze_mad extends Role_critical_mad {
  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($target_uname)) {
	DB::$ROOM->StoreEvent(DB::$ROOM->date, DeadReason::BLIND_VOTE);
	DB::$ROOM->StoreEvent(DB::$ROOM->date, DeadReason::BLIND_VOTE, 1);
	DB::$ROOM->StoreDead(null, DeadReason::BLIND_VOTE);
	return;
      }
    }
  }
}
