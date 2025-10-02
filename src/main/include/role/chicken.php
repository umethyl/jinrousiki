<?php
/*
  ◆小心者 (chicken)
  ○仕様
  ・ショック死：得票
*/
class Role_chicken extends Role {
  //ショック死判定 (セット済み > 対象外 > 個別判定)
  final public function SuddenDeath() {
    $type = VoteDayElement::SUDDEN_DEATH;
    if (false === is_null($this->GetStack($type))) {
      return;
    } elseif ($this->CallParent('IgnoreSuddenDeath')) {
      return;
    } elseif ($this->CallParent('IsSuddenDeath')) {
      $this->SetStack($this->CallParent('GetSuddenDeathType'), $type);
    }
  }

  //ショック死判定対象外判定
  protected function IgnoreSuddenDeath() {
    return false;
  }

  //ショック死判定対象外判定 (憑依/回避)
  final protected function IgnoreSuddenDeathAvoid() {
    return false === $this->IsRealActor() || RoleUser::IsAvoidLovers($this->GetActor(), true);
  }

  //ショック死セット判定
  protected function IsSuddenDeath() {
    return $this->CountVotePollUser() > 0;
  }

  //ショック死死因取得
  protected function GetSuddenDeathType() {
    return 'CHICKEN';
  }

  //投票先人数取得
  final protected function CountVoteKillTargetUser() {
    $stack = $this->GetStack(VoteDayElement::POLL_LIST);
    return ArrayFilter::GetInt($stack, $this->GetVoteKillUname());
  }

  //得票人数取得
  final protected function CountVotePollUser() {
    $stack = $this->GetStack(VoteDayElement::POLL_LIST);
    return ArrayFilter::GetInt($stack, $this->GetUname());
  }

  //突然死処理
  final protected function SuddenDeathKill($id) {
    DB::$USER->SuddenDeath($id, DeadReason::SUDDEN_DEATH, $this->CallParent('GetSuddenDeathType'));
  }
}
