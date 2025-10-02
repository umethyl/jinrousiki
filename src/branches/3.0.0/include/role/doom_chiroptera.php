<?php
/*
  ◆蝉蝙蝠 (doom_chiroptera)
  ○仕様
  ・ショック死：7日目
*/
class Role_doom_chiroptera extends Role {
  public $mix_in = array('chicken');
  public $sudden_death = 'SEALED';

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('partner_header', 7, 'sudden_death_footer');
  }

  public function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || $this->GetActor()->IsAvoidLovers(true);
  }

  public function IsSuddenDeath() {
    return DB::$ROOM->IsDate(7);
  }
}
