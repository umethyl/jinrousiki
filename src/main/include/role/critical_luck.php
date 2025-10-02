<?php
/*
  ◆痛恨 (critical_luck)
  ○仕様
  ・役職表示：無し
  ・得票数：+100 (5% / 天候「烈日」)
  ・得票数補正通知：急所通知
*/
RoleLoader::LoadFile('upper_luck');
class Role_critical_luck extends Role_upper_luck {
  protected function IgnoreImage() {
    return true;
  }

  protected function IgnoreFilterVotePoll() {
    return false === DB::$ROOM->IsEvent('critical') && false === Lottery::Percent(5);
  }

  protected function GetVotePollCount() {
    return 100;
  }

  protected function NoticeFilterVotePoll() {
    if (DB::$ROOM->IsOption('notice_critical')) {
      DB::$ROOM->StoreDead($this->GetActor()->handle_name, DeadReason::ACTIVE_CRITICAL_LUCK);
    }
  }
}
