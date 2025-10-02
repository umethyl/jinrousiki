<?php
/*
  ◆巫女 (medium)
  ○仕様
  ・能力結果：突然死
*/
class Role_medium extends Role {
  public $result = RoleAbility::MEDIUM;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  //判定結果登録 (システムメッセージ)
  final public function InsertResult() {
    $flag = false; //巫女の出現判定
    foreach (DB::$USER->GetRole() as $role => $list) {
      if (RoleDataManager::IsMain($role) && RoleDataManager::IsGroup($role, $this->role)) {
	$flag = true;
	break;
      }
    }
    if (! $flag) return;

    $stack = []; //突然死者を収集
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsOn(UserMode::SUICIDE)) {
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
