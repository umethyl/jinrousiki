<?php
/*
  ◆死の宣告 (death_warrant)
  ○仕様
  ・表示：発動日以内
  ・ショック死：発動当日
*/
RoleManager::LoadFile('febris');
class Role_death_warrant extends Role_febris {
  public $sudden_death = 'WARRANT';

  protected function IgnoreAbility() {
    return $this->GetDoomDate() < DB::$ROOM->date;
  }

  protected function GetDoomDate() {
    return $this->GetActor()->GetDoomDate($this->role);
  }
}
