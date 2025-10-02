<?php
/*
  ◆雑草魂 (upper_luck)
  ○仕様
  ・得票数：+4 (2日目) / -2 (3日目以降)
*/
class Role_upper_luck extends Role {
  //投票数補正処理
  final public function FilterVotePoll() {
    if ($this->CallParent('IgnoreFilterVotePoll')) return false;

    $count = $this->CallParent('GetVotePollCount');
    if (! $this->CallParent('IsUpdateFilterVotePoll')) {
      $count += $this->GetStack(VoteDayElement::POLL_NUMBER);
    }
    $this->CallParent('NoticeFilterVotePoll');
    //Text::p($count, "◆VotePoll [$this->role]");
    $this->SetStack($count, VoteDayElement::POLL_NUMBER);
  }

  //得票数補正無効判定
  protected function IgnoreFilterVotePoll() {
    return false;
  }

  //得票数補正値取得
  protected function GetVotePollCount() {
    return DB::$ROOM->IsDate(2) ? 4 : -2;
  }

  //得票数上書き判定
  protected function IsUpdateFilterVotePoll() {
    return false;
  }

  //得票数補正通知
  protected function NoticeFilterVotePoll() {}
}
