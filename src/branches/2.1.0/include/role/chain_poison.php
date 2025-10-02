<?php
/*
  ◆連毒者 (chain_poison)
  ○仕様
  ・役職表示：村人
  ・毒：特殊
*/
class Role_chain_poison extends Role {
  public $display_role = 'human';

  function IsPoison() { return false; }

  //毒処理
  function Poison(User $user) {
    RoleManager::$actor = DB::$USER->ByVirtual($user->user_no); //解毒判定
    RoleManager::$actor->detox = false;
    foreach (RoleManager::LoadFilter('detox') as $filter) $filter->Detox();
    if (RoleManager::$actor->detox) return;

    $stack = array();
    foreach (DB::$USER->GetLivingUsers(true) as $uname) { //生存者から常時対象外の役職を除く
      $user = DB::$USER->ByRealUname($uname);
      if (! $user->IsAvoid(true)) $stack[] = $user->user_no;
    }
    //Text::p($stack, "Target [{$this->role}]");

    $count = 1; //連鎖カウントを初期化
    while ($count > 0) {
      $count--;
      shuffle($stack); //配列をシャッフル
      //Text::p($stack, $count);
      for ($i = 0; $i < 2; $i++) {
	if (count($stack) < 1) return;
	$id = array_shift($stack);
	$target = DB::$USER->ByReal($id);

	if ($target->IsActive('resist_wolf')) { //抗毒判定
	  $target->LostAbility();
	  $stack[] = $id;
	  continue;
	}
	DB::$USER->Kill($id, 'POISON_DEAD'); //死亡処理

	if (! $target->IsRole($this->role)) continue; //連鎖判定
	RoleManager::$actor = DB::$USER->ByVirtual($target->user_no); //解毒判定
	RoleManager::$actor->detox = false;
	foreach (RoleManager::LoadFilter('detox') as $filter) $filter->Detox();
	if (! RoleManager::$actor->detox) $count++;
      }
    }
  }
}
