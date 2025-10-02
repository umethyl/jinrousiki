<?php
/*
  ◆凍傷 (frostbite)
  ○仕様
  ・ショック死：発動当日に無得票
*/
RoleManager::LoadFile('febris');
class Role_frostbite extends Role_febris {
  public $sudden_death = 'FROSTBITE';

  protected function GetResultFooter() {
    return $this->role . '_footer';
  }

  public function IsSuddenDeath() {
    return parent::IsSuddenDeath() && $this->GetVotedCount() == 0;
  }
}
