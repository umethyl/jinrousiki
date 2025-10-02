<?php
/*
  ◆妖狐 (fox)
  ○仕様
  ・仲間表示：妖狐系・子狐系
  ・人狼襲撃耐性：有り
*/
class Role_fox extends Role {
  public $result = 'FOX_EAT';
  public $resist_wolf = true;

  protected function OutputPartner() {
    if ($this->GetActor()->IsLonely()) return;
    $fox_list       = array(); //妖狐系
    $child_fox_list = array(); //子狐系
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsRole('possessed_fox')) {
	$fox_list[] = $user->GetName(); //憑依追跡
      }
      elseif ($user->IsFoxCount()) {
	if ($user->IsChildFox()) {
	  $child_fox_list[] = $user->handle_name;
	}
	elseif (! $user->IsLonely()) {
	  $fox_list[] = $user->handle_name;
	}
      }
      elseif ($user->IsRoleGroup('scarlet')) {
	$child_fox_list[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($fox_list, 'fox_partner');
    RoleHTML::OutputPartner($child_fox_list, 'child_fox_partner');
  }

  protected function IgnoreResult() {
    return ! $this->resist_wolf || DB::$ROOM->date < 2 || DB::$ROOM->IsOption('seal_message');
  }

  //人狼襲撃カウンター
  public function FoxEatCounter(User $user) {}
}
