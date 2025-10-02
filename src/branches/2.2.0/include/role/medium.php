<?php
/*
  ◆巫女 (medium)
*/
class Role_medium extends Role {
  public $result = 'MEDIUM_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->date > 1) $this->OutputAbilityResult($this->result);
  }

  //判定結果登録 (システムメッセージ)
  final function InsertResult() {
    $flag = false; //巫女の出現判定
    foreach (DB::$USER->role as $role => $list) {
      if (RoleData::IsMain($role) && RoleData::IsGroup($role, $this->role)) {
	$flag = true;
	break;
      }
    }
    if (! $flag) return;

    $stack = array(); //突然死者を収集
    foreach (DB::$USER->rows as $user) {
      if ($user->suicide_flag) {
	$virtual = $user->GetVirtual();
	$stack[$virtual->id] = $user->GetCamp(); //本体の所属陣営を記録;
      }
    }

    ksort($stack); //出力は仮想ユーザ順
    foreach ($stack as $id => $camp) {
      $user = DB::$USER->ByID($id);
      DB::$ROOM->ResultAbility($this->result, $camp, $user->handle_name);
    }
  }
}
