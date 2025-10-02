<?php
/*
  ◆人形遣い (doll_master)
  ○仕様
  ・勝利：通常
  ・仲間表示：なし
  ・身代わり：人形
*/
RoleManager::LoadFile('doll');
class Role_doll_master extends Role_doll {
  public $mix_in = array('protected');

  protected function OutputPartner() {
    return;
  }

  public function Win($winner) {
    return true;
  }

  public function IsSacrifice(User $user) {
    return $this->IsDoll($user);
  }

  //人形生存数取得
  final protected function GetDollCount() {
    $count = 0;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive(true) && ! $user->revive_flag && $this->IsDoll($user)) {
	$count++;
      }
    }
    return $count;
  }
}
