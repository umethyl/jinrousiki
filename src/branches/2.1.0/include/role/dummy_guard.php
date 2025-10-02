<?php
/*
  ◆夢守人 (dummy_guard)
  ○仕様
*/
RoleManager::LoadFile('guard');
class Role_dummy_guard extends Role_guard {
  public $display_role = 'guard';

  function SetGuard($uname) {
    if (! DB::$ROOM->IsEvent('no_dream')) $this->AddStack($uname); //スキップ判定 (熱帯夜)
    return false;
  }

  //夢防衛
  function GuardDream(User $user, $uname) {
    if (! in_array($uname, $this->GetStack())) return false;
    $flag = false;
    foreach (array_keys($this->GetStack(), $uname) as $guard_uname) { //護衛者を検出
      $guarder = DB::$USER->ByUname($guard_uname);
      if ($guarder->IsDead(true)) continue; //直前に死んでいたら無効

      $flag = true;
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $user->handle_name, $guarder->user_no);
      }
    }
    if ($flag) DB::$USER->Kill($user->user_no, 'HUNTED');
    return $flag;
  }

  //護衛処理
  function DreamGuard(array &$list) {
    foreach ($this->GetStack() as $uname => $target_uname) {
      $user = DB::$USER->ByUname($uname);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効

      $target = DB::$USER->ByUname($target_uname);
      if (($target->IsRole('dream_eater_mad') || $target->IsRoleGroup('fairy')) &&
	  $target->IsLive(true)) { //狩り判定 (獏・妖精系)
	$list[$user->user_no] = $target;
      }
      //常時護衛成功メッセージだけが出る
      $name = DB::$USER->GetHandleName($target->uname, true);
      DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $name, $user->user_no);
    }
  }

  //狩り処理
  function DreamHunt(array $list) {
    foreach ($list as $id => $target) {
      DB::$USER->Kill($target->user_no, 'HUNTED');
      //憑依能力者は対象外なので仮想ユーザを引く必要なし
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $target->handle_name, $id);
      }
    }
  }
}
