<?php
/*
  ◆妖狐 (fox)
  ○仕様
  ・仲間表示：妖狐枠(憑依追跡)・子狐枠
  ・能力結果：人狼襲撃 (天啓封印あり)
  ・人狼襲撃耐性：有り
*/
class Role_fox extends Role {
  public $result = RoleAbility::FOX;

  protected function IgnorePartner() {
    return RoleUser::IsLonely($this->GetActor());
  }

  protected function GetPartner() {
    $main  = 'fox_partner';       //妖狐系
    $sub   = 'child_fox_partner'; //子狐系
    $stack = [$main => [], $sub => []];
    foreach (DB::$USER->Get() as $user) {
      if ($this->IsActor($user)) {
	continue;
      }

      if ($user->IsRole('possessed_fox')) {
	$stack[$main][] = $user->GetName(); //憑依追跡
      } elseif ($user->IsMainGroup(CampGroup::FOX)) {
	if (false === RoleUser::IsLonely($user)) {
	  $stack[$main][] = $user->handle_name;
	}
      } elseif ($user->IsMainGroup(CampGroup::CHILD_FOX) || $user->IsRoleGroup('scarlet')) {
	$stack[$sub][] = $user->handle_name;
      }
    }
    return $this->FilterPartner($stack);
  }

  //仲間表示フィルタリング
  protected function FilterPartner(array $list) {
    return $list;
  }

  protected function IgnoreResult() {
    return DateBorder::PreTwo() || false === $this->ResistWolfEatFox();
  }

  //妖狐人狼襲撃耐性判定
  public function ResistWolfEatFox() {
    return true;
  }

  //人狼襲撃カウンター
  public function WolfEatFoxCounter(User $user) {}
}
