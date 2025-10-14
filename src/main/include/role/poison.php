<?php
/*
  ◆埋毒者 (poison)
  ○仕様
  ・毒：常時 / 制限なし
*/
class Role_poison extends Role {
  //毒発動判定
  public function IsPoison() {
    return true;
  }

  //処刑毒死候補者選出
  final public function GetVoteKillPoisonTarget(array $list) {
    $stack     = [];
    $aspirator = [];
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsDead(true) || $this->AvoidPoison($user)) {
	continue;
      }

      if ($this->CallParent('IsPoisonTarget', $user)) {
	if ($user->IsRole('aspirator')) { //吸毒者判定
	  $aspirator[] = $user->id;
	} else {
	  $stack[]     = $user->id;
	}
      }
    }
    return count($aspirator) > 0 ? $aspirator : $stack;
  }

  //毒回避判定
  private function AvoidPoison(User $user) {
    return $user->IsRole(RoleFilterData::$avoid_poison) || RoleUser::Avoid($user, true);
  }

  //毒対象者判定
  protected function IsPoisonTarget(User $user) {
    return true;
  }

  //人狼襲撃毒死無効判定
  final public function IgnorePoisonEat(User $user) {
    return false === $this->CallParent('IsPoisonTarget', $user);
  }
}
