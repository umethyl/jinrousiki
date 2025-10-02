<?php
/*
  ◆熱病 (febris)
  ○仕様
  ・表示：当日限定
  ・ショック死：発動当日
*/
RoleManager::LoadFile('chicken');
class Role_febris extends Role_chicken {
  public $sudden_death = 'FEBRIS';

  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  protected function IgnoreImage() {
    return true;
  }

  protected function OutputAddResult() {
    $header = $this->role . '_header';
    $footer = $this->GetResultFooter();
    RoleHTML::OutputAbilityResult($header, $this->GetDoomDate(), $footer);
  }

  //ショック死発動日取得
  protected function GetDoomDate() {
    return DB::$ROOM->date;
  }

  //結果表示フッタ取得
  protected function GetResultFooter() {
    return 'sudden_death_footer';
  }

  public function IsSuddenDeath() {
    return $this->IsDoom();
  }
}
