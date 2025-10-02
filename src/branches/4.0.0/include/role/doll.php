<?php
/*
  ◆上海人形 (doll)
  ○仕様
  ・勝利：人形遣い死亡
  ・仲間表示：人形遣い枠
*/
class Role_doll extends Role {
  protected function GetPartner() {
    $flag  = $this->IsDisplayDoll();
    $main  = 'doll_master_list'; //人形遣い枠
    $sub   = 'doll_partner';     //人形
    $stack = [$main => [], $sub => []];
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->IsDisplayDollMaster($user)) {
	$stack[$main][] = $user->handle_name;
      }
      if ($flag && $this->IsDoll($user)) {
	$stack[$sub][] = $user->handle_name;
      }
    }
    if (! $flag) unset($stack[$sub]);
    return $stack;
  }

  //人形表示判定
  protected function IsDisplayDoll() {
    return false;
  }

  //人形遣い枠表示判定
  final protected function IsDisplayDollMaster(User $user) {
    if ($this->IsDollMaster($user)) { //人形遣い
      return ! $user->IsRole('toy_doll_master') || DB::$ROOM->date > 3;
    }
    return $user->IsRole('puppet_mage') || $user->IsRoleGroup('scarlet'); //特殊・紅系
  }

  //人形遣い判定
  final protected function IsDollMaster(User $user) {
    return $user->IsRoleGroup('doll_master');
  }

  //人形判定
  final protected function IsDoll(User $user) {
    return $user->IsMainGroup(CampGroup::DOLL) && ! $this->IsDollMaster($user);
  }

  public function Win($winner) {
    $this->SetStack('doll', 'class');
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $this->IsDollMaster($user)) {
	return false;
      }
    }
    return true;
  }
}
