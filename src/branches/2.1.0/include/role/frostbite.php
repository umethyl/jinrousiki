<?php
/*
  ◆凍傷 (frostbite)
  ○仕様
  ・ショック死：発動当日に無得票
*/
RoleManager::LoadFile('febris');
class Role_frostbite extends Role_febris {
  public $sudden_death = 'FROSTBITE';

  protected function OutputResult() {
    $date = DB::$ROOM->date;
    RoleHTML::OutputAbilityResult($this->role . '_header', $date, $this->role . '_footer');
  }

  function IsSuddenDeath() { return parent::IsSuddenDeath() && $this->GetVotedCount() == 0; }
}
