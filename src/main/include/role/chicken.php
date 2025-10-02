<?php
/*
  ◆小心者 (chicken)
  ○仕様
  ・ショック死：得票
*/
class Role_chicken extends Role {
  //ショック死判定 (セット済み > 対象外 > 個別判定)
  final public function SuddenDeath() {
    $type = 'sudden_death';
    if (! is_null($this->GetStack($type))) {
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

  //ショック死セット判定
  protected function IsSuddenDeath() {
    return $this->CountVoted() > 0;
  }

  //ショック死死因取得
  protected function GetSuddenDeathType() {
    return 'CHICKEN';
  }

  //投票先人数取得
  final protected function CountVoteTarget() {
    return ArrayFilter::GetInt($this->GetStack('count'), $this->GetVoteTargetUname());
  }

  //得票人数取得
  final protected function CountVoted() {
    return ArrayFilter::GetInt($this->GetStack('count'), $this->GetUname());
  }

  //突然死処理
  final protected function SuddenDeathKill($id) {
    DB::$USER->SuddenDeath($id, DeadReason::SUDDEN_DEATH, $this->CallParent('GetSuddenDeathType'));
  }
}
