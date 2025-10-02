<?php
/*
  ◆人形遣い (doll_master)
  ○仕様
  ・勝利：通常
  ・仲間表示：なし
  ・身代わり：人形
*/
RoleLoader::LoadFile('doll');
class Role_doll_master extends Role_doll {
  public $mix_in = ['protected'];

  protected function IgnorePartner() {
    return true;
  }

  protected function IsSacrifice(User $user) {
    return $this->IsDoll($user);
  }

  //人形生存数取得
  final protected function CountDoll() {
    $count = 0;
    foreach (DB::$USER->Get() as $user) {
      if (! $user->IsInactive() && $this->IsDoll($user)) {
	$count++;
      }
    }
    return $count;
  }

  public function Win($winner) {
    return true;
  }
}
