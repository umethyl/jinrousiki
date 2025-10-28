<?php
/*
  ◆凍傷 (frostbite)
  ○仕様
  ・ショック死：発動当日に無得票
*/
RoleLoader::LoadFile('febris');
class Role_frostbite extends Role_febris {
  protected function GetSuddenDeathResultFooter() {
    return $this->role . '_footer';
  }

  protected function IgnoreSuddenDeath() {
    return false;
  }

  protected function IsSuddenDeath() {
    return parent::IsSuddenDeath() && $this->CountVotePollUser() == 0;
  }

  protected function GetSuddenDeathType() {
    return 'FROSTBITE';
  }

  //凍傷実行処理
  final public function SetFrostbite() {
    foreach ($this->GetStackKey() as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsLive(true)) {
	$user->AddDoom(1, $this->role);
      }
    }
  }
}
