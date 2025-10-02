<?php
/*
  ◆小心者 (chicken)
  ○仕様
  ・ショック死：得票
*/
class Role_chicken extends Role {
  public $sudden_death = 'CHICKEN';

  //ショック死判定
  final public function SuddenDeath() {
    $type = 'sudden_death';
    if ($this->GetStack($type) != '') return; //すでにセットされていたらスキップ

    $class = $this->GetClass($method = 'IgnoreSuddenDeath');
    if ($class->$method()) return;

    $class = $this->GetClass($method = 'IsSuddenDeath');
    if ($class->$method()) $this->SetStack($this->GetProperty($type), $type);
  }

  //ショック死判定対象外判定
  public function IgnoreSuddenDeath() {
    return false;
  }

  //ショック死セット判定
  public function IsSuddenDeath() {
    return $this->GetVotedCount() > 0;
  }

  //投票先人数取得
  final protected function GetVoteTargetCount() {
    $count = $this->GetStack('count');
    $uname = $this->GetVoteTargetUname();
    return array_key_exists($uname, $count) ? $count[$uname] : 0;
  }

  //得票人数取得
  final protected function GetVotedCount() {
    $count = $this->GetStack('count');
    $uname = $this->GetUname();
    return array_key_exists($uname, $count) ? $count[$uname] : 0;
  }
}
