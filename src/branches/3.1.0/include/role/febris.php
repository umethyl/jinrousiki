<?php
/*
  ◆熱病 (febris)
  ○仕様
  ・表示：当日限定
  ・能力結果：発動宣告
  ・ショック死：発動当日
*/
RoleLoader::LoadFile('chicken');
class Role_febris extends Role_chicken {
  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  protected function IgnoreImage() {
    return true;
  }

  protected function OutputAddResult() {
    $header = $this->GetImage() . '_header';
    $date   = $this->GetDoomDate();
    $footer = $this->GetResultFooter();
    RoleHTML::OutputAbilityResult($header, $date, $footer);
  }

  //ショック死発動日取得
  protected function GetDoomDate() {
    return DB::$ROOM->date;
  }

  //結果表示フッタ取得
  protected function GetResultFooter() {
    return 'sudden_death_footer';
  }

  protected function IgnoreSuddenDeath() {
    $user = $this->GetActor()->GetReal();
    return $user->IsRoleGroup('fortitude') || $this->IgnoreSuddenDeathFebris();
  }

  //熱病追加ショック死判定対象外判定
  protected function IgnoreSuddenDeathFebris() {
    return false;
  }

  protected function IsSuddenDeath() {
    return $this->IsDoom();
  }

  protected function GetSuddenDeathType() {
    return 'FEBRIS';
  }
}
