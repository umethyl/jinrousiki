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

  protected function OutputResult() {
    if (DB::$ROOM->date < 1 || DB::$ROOM->IsOption('seal_message')) return;
    $this->OutputAbilityResult('GUARD_SUCCESS'); //護衛結果
    $this->OutputAbilityResult('GUARD_HUNTED');  //狩り結果
  }

  function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'guard_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日は護衛できません'; }

  //護衛先セット
  function SetGuard(User $user) {
    if (DB::$ROOM->IsEvent('no_contact')) return false; //スキップ判定 (花曇)
    $this->AddStack($user->id, 'guard');
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($this->GetActor(), $user->id)) break;
    }
    return true;
  }

  //護衛
  function Guard(User $user) {
    $stack = array(); //護衛者検出
    foreach (RoleManager::LoadFilter('guard') as $filter) {
      $stack = array_merge($stack, $filter->GetGuard($user->id));
    }
    //Text::p($stack, sprintf('◆List [gurad/%s]',$this->GetVoter()->uname));

    $result  = false;
    $half    = DB::$ROOM->IsEvent('half_guard'); //曇天
    $limited = ! DB::$ROOM->IsEvent('full_guard') && $this->IsGuardLimited($user); //護衛制限判定
    foreach ($stack as $id) {
      $actor = DB::$USER->ByID($id);
      if ($actor->IsDead(true)) continue; //直前に死んでいたら無効

      $filter = RoleManager::LoadMain($actor);
      if ($ignore = $filter->IgnoreGuard()) continue; //個別護衛失敗判定
      $result |= ! ($half && Lottery::Bool()) && (! $limited || is_null($ignore));

      $filter->GuardAction(); //護衛実行処理
      //護衛成功メッセージを登録
      $this->AddSuccess($actor->id, 'guard_success'); //成功者を登録
      if (! DB::$ROOM->IsOption('seal_message') && $actor->IsFirstGuardSuccess($user->id)) {
	DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $user->GetName(), $actor->id);
      }
    }
    return $result;
  }

  //護衛者検出
  final function GetGuard($id) { return array_keys($this->GetStack('guard'), $id); }

  //護衛制限判定
  private function IsGuardLimited(User $user) {
    return $user->IsRole(
      'step_mage', 'emissary_necromancer', 'reporter', 'detective_common', 'sacrifice_common',
      'spell_common', 'clairvoyance_scanner', 'soul_wizard', 'barrier_wizard', 'pierrot_wizard',
      'doll_master') || $user->IsMainGroup('assassin') ||
      ($user->IsRoleGroup('priest') &&
       ! $user->IsRole('crisis_priest', 'widow_priest', 'revive_priest'));
  }

  //護衛失敗判定
  function IgnoreGuard() { return false; }

  //護衛処理
  function GuardAction() {}

  //狩り
  function Hunt(User $user) {
    //対象が身代わり死していた場合はスキップ
    if (in_array($user->uname, $this->GetStack('sacrifice')) || ! $this->IsHunt($user)) {
      return false;
    }
    DB::$USER->Kill($user->id, 'HUNTED');
    if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
      DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $user->GetName(), $this->GetID());
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
      ($user->IsMainGroup('mad') &&
       ! $user->IsRole('mad', 'fanatic_mad', 'whisper_mad', 'swindle_mad', 'step_mad',
		       'therian_mad', 'revive_mad', 'immolate_mad'));
  }
}
