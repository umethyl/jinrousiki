<?php
/*
  ◆蝉蝙蝠 (doom_chiroptera)
  ○仕様
  ・能力結果：ショック死予定日
  ・ショック死：7日目
*/
class Role_doom_chiroptera extends Role {
  public $mix_in = array('chicken');

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('partner_header', 7, 'sudden_death_footer');
  }

  protected function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || RoleUser::IsAvoidLovers($this->GetActor(), true);
  }

  protected function IsSuddenDeath() {
    return DB::$ROOM->IsDate(7);
  }

  protected function GetSuddenDeathType() {
    return 'SEALED';
  }
}
