<?php
/*
  ◆狩人 (guard)
  ○仕様
  ・護衛失敗：通常
  ・護衛処理：なし
  ・狩り：通常
*/
class Role_guard extends Role {
  public $action = 'GUARD_DO';
  public $ignore_message = '初日は護衛できません';

  protected function OutputResult() {
    if (DB::$ROOM->date < 1 || DB::$ROOM->IsOption('seal_message')) return;
    $this->OutputAbilityResult('GUARD_SUCCESS'); //護衛結果
    $this->OutputAbilityResult('GUARD_HUNTED');  //狩り結果
  }

  function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'guard_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  //護衛先セット
  function SetGuard($uname) {
    if (DB::$ROOM->IsEvent('no_contact')) return false; //スキップ判定 (花曇)
    $this->AddStack($uname, 'guard');
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($this->GetActor(), $uname)) break;
    }
    return true;
  }

  //護衛
  function Guard(User $user, $flag = false) {
    $stack = array(); //護衛者検出
    foreach (RoleManager::LoadFilter('guard') as $filter) $filter->GetGuard($user->uname, $stack);
    //Text::p($stack, 'List [gurad/' . $this->GetVoter()->uname . ']');

    $result  = false;
    $half    = DB::$ROOM->IsEvent('half_guard'); //曇天
    $limited = ! DB::$ROOM->IsEvent('full_guard') && $this->IsGuardLimited($user); //護衛制限判定
    foreach ($stack as $uname) {
      $actor  = DB::$USER->ByUname($uname);
      if ($actor->IsDead(true)) continue; //直前に死んでいたら無効

      $filter = RoleManager::LoadMain($actor);
      if ($failed = $filter->GuardFailed()) continue; //個別護衛失敗判定
      $result |= ! ($half && mt_rand(0, 1) > 0) && (! $limited || is_null($failed));

      $filter->GuardAction($this->GetWolfVoter(), $flag); //護衛実行処理
      //護衛成功メッセージを登録
      $this->AddSuccess($actor->user_no, 'guard_success'); //成功者を登録
      if (! DB::$ROOM->IsOption('seal_message') && $actor->IsFirstGuardSuccess($user->uname)) {
	$target = DB::$USER->GetHandleName($user->uname, true);
	DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $target, $actor->user_no);
      }
    }
    return $result;
  }

  //護衛者検出
  function GetGuard($uname, array &$list) {
    $list = array_keys($this->GetStack('guard'), $uname);
  }

  //護衛制限判定
  private function IsGuardLimited(User $user) {
    return $user->IsRole(
      'emissary_necromancer', 'reporter', 'detective_common', 'sacrifice_common', 'spell_common',
      'clairvoyance_scanner', 'soul_wizard', 'barrier_wizard', 'pierrot_wizard', 'doll_master') ||
      ($user->IsRoleGroup('priest') &&
       ! $user->IsRole('crisis_priest', 'widow_priest', 'revive_priest')) ||
      $user->IsRoleGroup('assassin');
  }

  //護衛失敗判定
  function GuardFailed() { return false; }

  //護衛処理
  function GuardAction(User $user, $flag) {}

  //狩り
  function Hunt(User $user) {
    //対象が身代わり死していた場合はスキップ
    if (in_array($user->uname, $this->GetStack('sacrifice')) || ! $this->IsHunt($user)) {
      return false;
    }
    DB::$USER->Kill($user->user_no, 'HUNTED');
    if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
      $target = DB::$USER->GetHandleName($user->uname, true);
      DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $target, $this->GetID());
    }
  }

  //狩り対象判定
  protected function IsHunt(User $user) {
    return $user->IsRole(
      'phantom_fox', 'voodoo_fox', 'revive_fox', 'doom_fox', 'trap_fox', 'cursed_fox',
      'cursed_angel', 'incubus_vampire', 'succubus_vampire', 'doom_vampire', 'sacrifice_vampire',
      'soul_vampire', 'poison_chiroptera', 'cursed_chiroptera', 'boss_chiroptera', 'cursed_avenger',
      'critical_avenger') ||
      ($user->IsRole('possessed_fox') && count($user->GetPartner('possessed_target', true)) < 1) ||
      ($user->IsRoleGroup('mad') &&
       ! $user->IsRole('mad', 'fanatic_mad', 'whisper_mad', 'swindle_mad', 'therian_mad',
		       'revive_mad', 'immolate_mad'));
  }
}
