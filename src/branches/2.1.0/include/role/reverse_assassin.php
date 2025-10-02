<?php
/*
  ◆反魂師 (reverse_assassin)
  ○仕様
  ・暗殺：反魂
*/
RoleManager::LoadFile('assassin');
class Role_reverse_assassin extends Role_assassin {
  function Assassin(User $user) {
    RoleManager::$get->reverse_assassin[$this->GetActor()->uname] = $user->uname;
  }

  function AssassinKill() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      $target = DB::$USER->ByUname($target_uname);
      if ($target->IsLive(true)) {
	DB::$USER->Kill($target->user_no, 'ASSASSIN_KILLED');
      }
      elseif (! $target->IsLovers()) {
	RoleManager::$get->reverse[$target_uname] = ! RoleManager::$get->reverse[$target_uname];
      }
    }
  }

  //反魂処理
  function Resurrect() {
    $role = 'possessed';
    foreach ($this->GetStack('reverse') as $uname => $flag) {
      if (! $flag) continue;
      $user = DB::$USER->ByUname($uname);
      if ($user->IsPossessedGroup()) { //憑依能力者対応
	if ($user->revive_flag) continue; //蘇生済みならスキップ

	$virtual = DB::$USER->ByVirtual($user->user_no);
	if ($user != $virtual) { //憑依中ならリセット
	  $user->ReturnPossessed('possessed_target'); //本人
	  $virtual->ReturnPossessed($role); //憑依先
	}

	//憑依予定者が居たらキャンセル
	if (array_key_exists($user->uname, $this->GetStack($role))) {
	  $user->possessed_reset  = false;
	  $user->possessed_cancel = true;
	}
	elseif (in_array($user->uname, $this->GetStack($role))) {
	  //憑依中の犬神に憑依しようとした憑狼を検出
	  $stack = array_keys($this->GetStack($role), $user->uname);
	  DB::$USER->ByUname($stack[0])->possessed_cancel = true;
	}

	//特殊ケースなのでベタに処理
	$virtual->Update('live', 'live');
	$virtual->revive_flag = true;
	DB::$ROOM->ResultDead($virtual->handle_name, 'REVIVE_SUCCESS');
      }
      else {
	//憑依されていたらリセット
	if ($user != DB::$USER->ByReal($user->user_no)) $user->ReturnPossessed($role);
	$user->Revive(); //蘇生処理
      }
    }
  }
}
