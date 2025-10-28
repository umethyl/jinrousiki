<?php
/*
  ◆会心 (critical_voter)
  ○仕様
  ・役職表示：無し
  ・投票数：+100 (5% / 天候「烈日」)
  ・投票数補正通知：急所通知
*/
RoleLoader::LoadFile('authority');
class Role_critical_voter extends Role_authority {
  protected function IgnoreImage() {
    return true;
  }

  protected function IgnoreFilterVoteDo() {
    return false === DB::$ROOM->IsEvent('critical') && false === Lottery::Percent(5);
  }

  protected function GetVoteDoCount() {
    return 100;
  }

  protected function NoticeFilterVoteDo() {
    if (DB::$ROOM->IsOption('notice_critical')) {
      DB::$ROOM->StoreDead($this->GetActor()->handle_name, DeadReason::ACTIVE_CRITICAL_VOTER);
    }
  }
}
