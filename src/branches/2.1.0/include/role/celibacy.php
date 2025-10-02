<?php
/*
  ◆独身貴族 (celibacy)
  ○仕様
  ・ショック死：恋人からの得票
*/
RoleManager::LoadFile('chicken');
class Role_celibacy extends Role_chicken {
  public $sudden_death = 'CELIBACY';

  function SuddenDeath() {
    if ($this->IgnoreSuddenDeath()) return;
    foreach ($this->GetVotedUname() as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsLovers()) {
	$this->SetSuddenDeath($this->sudden_death);
	break;
      }
    }
  }
}
