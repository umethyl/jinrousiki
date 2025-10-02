<?php
/*
  ◆反魂師 (reverse_assassin)
  ○仕様
  ・暗殺：反魂 (生存 > 対象外 > 反魂)
*/
RoleLoader::LoadFile('assassin');
class Role_reverse_assassin extends Role_assassin {
  protected function Assassin(User $user) {
    $this->AddStack($user->id);
  }

  public function AssassinKill() {
    foreach ($this->GetStack() as $id => $target_id) {
      $target = DB::$USER->ByID($target_id);
      if ($target->IsLive(true)) {
	DB::$USER->Kill($target->id, DeadReason::ASSASSIN_KILLED);
      } elseif ($this->IgnoreResurrect($target)) {
	continue;
      } else {
	$stack = RoleManager::Stack()->Get('reverse');
	ArrayFilter::ReverseBool($stack, $target_id);
	RoleManager::Stack()->Set('reverse', $stack);
      }
    }
  }

  //反魂対象外判定
  private function IgnoreResurrect(User $user) {
    return $user->IsRole('lovers') || $user->IsMainGroup(CampGroup::DEPRAVER) ||
      RoleUser::IsDelayCopy($user);
  }

  //反魂処理
  public function Resurrect() {
    $role = 'possessed';
    foreach ($this->GetStack('reverse') as $id => $flag) {
      if (! $flag) continue;
      $user = DB::$USER->ByID($id);
      if (RoleUser::IsPossessed($user)) { //憑依能力者対応
	if ($user->IsOn(UserMode::REVIVE)) continue; //蘇生済みならスキップ

	$virtual = $user->GetVirtual();
	if (! $user->IsSame($virtual)) { //憑依中ならリセット
	  $user->ReturnPossessed('possessed_target'); //本人
	  $virtual->ReturnPossessed($role); //憑依先
	}

	//憑依予定者が居たらキャンセル
	if (RoleUser::IsPossessedTarget($user)) {
	  $user->Flag()->Off(UserMode::POSSESSED_RESET);
	  $user->Flag()->On(UserMode::POSSESSED_CANCEL);
	} elseif ($this->InStack($user->id, RoleVoteSuccess::POSSESSED)) {
	  //憑依中の犬神に憑依しようとした憑狼を検出
	  $stack = $this->GetStackKey(RoleVoteSuccess::POSSESSED, $user->id);
	  DB::$USER->ByID($stack[0])->Flag()->On(UserMode::POSSESSED_CANCEL);
	}

	//特殊ケースなのでベタに処理
	$virtual->UpdateLive(UserLive::LIVE);
	$virtual->Flag()->On(UserMode::REVIVE);
	DB::$ROOM->StoreDead($virtual->handle_name, DeadReason::REVIVE_SUCCESS);
      } else {
	if (! $user->IsSame(DB::$USER->ByReal($user->id))) { //憑依されていたらリセット
	  $user->ReturnPossessed($role);
	}
	$user->Revive(); //蘇生処理
      }
    }
  }
}
