<?php
/*
  ◆夢守人 (dummy_guard)
  ○仕様
  ・狩り：獏・妖精系
*/
RoleLoader::LoadFile('guard');
class Role_dummy_guard extends Role_guard {
  public $display_role = 'guard';

  protected function IgnoreSetGuard() {
    return DB::$ROOM->IsEvent('no_dream');
  }

  protected function SetGuardStack(User $user) {
    $this->AddStack($user->id);
  }

  //対夢食い護衛
  public function GuardDreamEat(User $user, $target_id) {
    $flag = false;
    foreach ($this->GetStackKey($this->role, $target_id) as $id) { //護衛者を検出
      $actor = DB::$USER->ByID($id);
      if ($actor->IsDead(true)) continue; //直前に死んでいたら無効

      $flag = true;
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->StoreAbility(RoleAbility::HUNTED, 'hunted', $user->handle_name, $actor->id);
      }
    }
    if ($flag) DB::$USER->Kill($user->id, DeadReason::HUNTED);
    return $flag;
  }

  //護衛処理
  public function DreamGuard(array &$list) {
    foreach ($this->GetStack() as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効

      $target = DB::$USER->ByID($target_id);
      //狩り判定
      if ($target->IsLive(true) && $this->IsHunt($target) &&
	  ! RoleUser::IsAvoidLovers($target, true)) {
	$list[$user->id] = $target;
      }

      if (! DB::$ROOM->IsOption('seal_message')) { //常時護衛成功メッセージだけが出る
	DB::$ROOM->StoreAbility(RoleAbility::GUARD, 'success', $target->GetName(), $user->id);
      }
    }
  }

  //狩り処理
  public function DreamHunt(array $list) {
    foreach ($list as $id => $target) {
      DB::$USER->Kill($target->id, DeadReason::HUNTED);
      //憑依能力者は対象外なので仮想ユーザを引く必要なし
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->StoreAbility(RoleAbility::HUNTED, 'hunted', $target->handle_name, $id);
      }
    }
  }

  protected function IsHunt(User $user) {
    return RoleUser::IsDreamTarget($user);
  }
}
