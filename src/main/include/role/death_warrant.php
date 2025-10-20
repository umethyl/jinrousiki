<?php
/*
  ◆死の宣告 (death_warrant)
  ○仕様
  ・表示：発動日以内
  ・ショック死：発動当日
*/
RoleLoader::LoadFile('febris');
class Role_death_warrant extends Role_febris {
  protected function IgnoreAbility() {
    return DateBorder::Up($this->GetDoomDate());
  }

  protected function GetDoomDate() {
    return $this->GetActor()->GetDoomDate($this->role);
  }

  protected function IgnoreSuddenDeath() {
    return $this->GetActor()->GetReal()->IsRoleGroup('fortitude');
  }

  protected function GetSuddenDeathType() {
    return 'WARRANT';
  }
}
