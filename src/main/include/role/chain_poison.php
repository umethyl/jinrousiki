<?php
/*
  ◆連毒者 (chain_poison)
  ○仕様
  ・役職表示：村人
  ・毒：特殊
*/
RoleLoader::LoadFile('poison');
class Role_chain_poison extends Role_poison {
  public $display_role = 'human';

  public function IsPoison() {
    return false;
  }

  //連毒処理
  public function ChainPoison() {
    if ($this->IsDetox($this->GetActor()->GetVirtual())) return; //解毒判定

    $stack     = array();
    $aspirator = array();
    foreach (DB::$USER->SearchLive(true) as $id => $uname) { //生存者から常時対象外の役職を除く
      $target = DB::$USER->ByReal($id);
      if (RoleUser::IsAvoid($target, true)) continue;
      $stack[] = $target->id;
      if ($target->IsRole('aspirator')) $aspirator[] = $target->id;
    }
    //Text::p($stack, "◆Target/Base [{$this->role}]");
    //Text::p($aspirator, "◆Target/aspirator [{$this->role}]");

    $count = 1; //連鎖カウントを初期化
    while ($count > 0) {
      $count--;
      //Text::p($stack, "◆Remain/{$count} [{$this->role}]");

      //-- 対象者選出 --//
      $target_stack = array();
      for ($i = 0; $i < 2; $i++) {
	if (count($stack) < 1) break;
	$id = Lottery::Get(count($aspirator) > 0 ? $aspirator : $stack);
	$target_stack[] = $id;
	ArrayFilter::Shrink($stack, $id);
	if (count($aspirator) > 0) ArrayFilter::Shrink($aspirator, $id);
      }
      //Text::p($target_stack, "◆Target [{$this->role}]");

      //-- 毒発動処理 --//
      foreach ($target_stack as $id) {
	$target = DB::$USER->ByReal($id);
	if ($target->IsActive('resist_wolf')) { //抗毒判定
	  $target->LostAbility();
	  $stack[] = $id; //対象者に再登録
	  if ($target->IsRole('aspirator')) {
	    $aspirator[] = $id;
	  }
	  continue;
	}
	DB::$USER->Kill($id, DeadReason::POISON_DEAD); //死亡処理

	if (! $target->IsRole($this->role)) continue; //連鎖判定
	if (! $this->IsDetox($target->GetVirtual())) $count++; //解毒判定
      }
    }
  }

  //解毒判定
  private function IsDetox(User $user) {
    //Text::p($user->uname, "◆Detox [{$this->role}]");
    $user->detox = false;
    RoleLoader::SetActor($user);
    foreach (RoleLoader::LoadFilter('detox') as $filter) {
      $filter->Detox();
    }
    return RoleLoader::GetActor()->detox;
  }
}
