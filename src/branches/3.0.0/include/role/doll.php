<?php
/*
  ◆上海人形 (doll)
  ○仕様
  ・勝利：人形遣い死亡
  ・仲間表示：人形遣い枠
*/
class Role_doll extends Role {
  protected function OutputPartner() {
    $stack = array();
    $flag  = $this->IsDisplayDoll();
    if ($flag) $doll_stack = array(); //人形表示判定
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->IsDisplayDollMaster($user)) {
	$stack[] = $user->handle_name;
      }
      if ($flag && $this->IsDoll($user)) {
	$doll_stack[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($stack, 'doll_master_list'); //人形遣い枠
    if ($flag) RoleHTML::OutputPartner($doll_stack, 'doll_partner'); //人形
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

  public function Win($winner) {
    $this->SetStack('doll', 'class');
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive() && $this->IsDollMaster($user)) return false;
    }
    return true;
  }

  //人形判定
  final protected function IsDoll(User $user) {
    return $user->IsMainGroup('doll') && ! $this->IsDollMaster($user);
  }

  //人形遣い判定
  final protected function IsDollMaster(User $user) {
    return $user->IsRoleGroup('doll_master');
  }
}
