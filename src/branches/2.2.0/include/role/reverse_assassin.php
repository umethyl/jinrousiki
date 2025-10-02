<?php
/*
  ◆反魂師 (reverse_assassin)
  ○仕様
  ・暗殺：反魂
*/
RoleManager::LoadFile('assassin');
class Role_reverse_assassin extends Role_assassin {
  function Assassin(User $user) { $this->AddStack($user->id); }

  function AssassinKill() {
    foreach ($this->GetStack() as $id => $target_id) {
      $target = DB::$USER->ByID($target_id);
      if ($target->IsLive(true)) {
	DB::$USER->Kill($target->id, 'ASSASSIN_KILLED');
      }
      elseif (! $target->IsLovers()) {
	$stack = RoleManager::GetStack('reverse');
	$stack[$target_id] = isset($stack[$target_id]) ? ! $stack[$target_id] : true;
	RoleManager::SetStack('reverse', $stack);
      }
    }
  }

  //反魂処理
  function Resurrect() {
    $role = 'possessed';
    foreach ($this->GetStack('reverse') as $id => $flag) {
      if (! $flag) continue;
      $user = DB::$USER->ByID($id);
      if ($user->IsPossessedGroup()) { //憑依能力者対応
	if ($user->revive_flag) continue; //蘇生済みならスキップ

	$virtual = $user->GetVirtual();
	if (! $user->IsSame($virtual)) { //憑依中ならリセット
	  $user->ReturnPossessed('possessed_target'); //本人
	  $virtual->ReturnPossessed($role); //憑依先
	}

	//憑依予定者が居たらキャンセル
	if (array_key_exists($user->id, $this->GetStack($role))) {
	  $user->possessed_reset  = false;
	  $user->possessed_cancel = true;
	}
	elseif (in_array($user->id, $this->GetStack($role))) {
	  //憑依中の犬神に憑依しようとした憑狼を検出
	  $stack = array_keys($this->GetStack($role), $user->id);
	  DB::$USER->ByID($stack[0])->possessed_cancel = true;
	}

	//特殊ケースなのでベタに処理
	$virtual->Update('live', 'live');
	$virtual->revive_flag = true;
	DB::$ROOM->ResultDead($virtual->handle_name, 'REVIVE_SUCCESS');
      }
      else {
	//憑依されていたらリセット
	if (! $user->IsSame(DB::$USER->ByReal($user->id))) $user->ReturnPossessed($role);
	$user->Revive(); //蘇生処理
      }
    }
  }
}
