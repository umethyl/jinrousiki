<?php
/*
  ◆巫女 (medium)
*/
class Role_medium extends Role {
  protected function OutputResult() {
    if (DB::$ROOM->date > 1) $this->OutputAbilityResult('MEDIUM_RESULT');
  }

  //判定結果登録 (システムメッセージ)
  final function InsertResult() {
    $flag  = false; //巫女の出現判定
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      $flag |= $user->IsRoleGroup($this->role);
      if ($user->suicide_flag) {
	$virtual_user = DB::$USER->ByVirtual($user->user_no);
	$id = $virtual_user->user_no;
	$stack[$id] = array('target' => $virtual_user->handle_name, 'result' => $user->GetCamp());
      }
    }
    if (! $flag) return;
    ksort($stack);
    foreach ($stack as $list) {
      DB::$ROOM->ResultAbility('MEDIUM_RESULT', $list['result'], $list['target']);
    }
  }
}
