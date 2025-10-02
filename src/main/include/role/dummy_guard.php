<?php
/*
  ◆夢守人 (dummy_guard)
  ○仕様
  ・狩り：獏・妖精系
*/
RoleManager::LoadFile('guard');
class Role_dummy_guard extends Role_guard {
  public $display_role = 'guard';

  function SetGuard(User $user) {
    if (! DB::$ROOM->IsEvent('no_dream')) $this->AddStack($user->id); //スキップ判定 (熱帯夜)
    return false;
  }

  //対夢食い護衛
  function GuardDream(User $user, $target_id) {
    $flag = false;
    foreach (array_keys($this->GetStack(), $target_id) as $id) { //護衛者を検出
      $actor = DB::$USER->ById($id);
      if ($actor->IsDead(true)) continue; //直前に死んでいたら無効

      $flag = true;
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $user->handle_name, $actor->id);
      }
    }
    if ($flag) DB::$USER->Kill($user->id, 'HUNTED');
    return $flag;
  }

  //護衛処理
  final function DreamGuard(array &$list) {
    foreach ($this->GetStack() as $id => $target_id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue; //直前に死んでいたら無効

      $target = DB::$USER->ById($target_id);
      if ($target->IsLive(true) && $this->IsHunt($target)) { //狩り判定
	$list[$user->id] = $target;
      }

      if (! DB::$ROOM->IsOption('seal_message')) { //常時護衛成功メッセージだけが出る
	DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $target->GetName(), $user->id);
      }
    }
  }

  //狩り処理
  final function DreamHunt(array $list) {
    foreach ($list as $id => $target) {
      DB::$USER->Kill($target->id, 'HUNTED');
      //憑依能力者は対象外なので仮想ユーザを引く必要なし
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $target->handle_name, $id);
      }
    }
  }

  protected function IsHunt(User $user) {
    return $user->IsRole('dream_eater_mad') || $user->IsMainGroup('fairy');
  }
}
