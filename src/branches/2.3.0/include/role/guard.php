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
  public $result = 'GUARD_SUCCESS';
  public $hunt   = true;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message');
  }

  protected function OutputAddResult() {
    if ($this->hunt) $this->OutputAbilityResult('GUARD_HUNTED');
    $this->OutputGuardAddResult();
  }

  //追加結果表示 (狩人系専用)
  protected function OutputGuardAddResult() {}

  public function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'guard_do', $this->action);
  }

  public function IsVote() {
    return DB::$ROOM->date > 1;
  }

  protected function GetIgnoreMessage() {
    return VoteRoleMessage::IMPOSSIBLE_FIRST_DAY;
  }

  //護衛先セット
  final public function SetGuard(User $user) {
    if ($this->IgnoreSetGuard()) return;
    $this->SetGuardStack($user);
    $this->SetGuardAction($user);
  }

  //護衛先セットスキップ判定
  protected function IgnoreSetGuard() {
    return DB::$ROOM->IsEvent('no_contact');
  }

  //護衛先情報登録
  protected function SetGuardStack(User $user) {
    $this->AddStack($user->id, 'guard');
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($this->GetActor(), $user->id)) break;
    }
  }

  //護衛先セット追加処理
  protected function SetGuardAction(User $user) {}

  //護衛
  final public function Guard(User $user) {
    $stack = array(); //護衛者検出
    foreach (RoleManager::LoadFilter('guard') as $filter) {
      $stack = array_merge($stack, $filter->GetGuard($user->id));
    }
    //Text::p($stack, sprintf('◆List [gurad/%s]', $this->GetVoter()->uname));

    $result  = false;
    $half    = DB::$ROOM->IsEvent('half_guard'); //曇天
    $limited = ! DB::$ROOM->IsEvent('full_guard') && $this->IsGuardLimited($user); //護衛制限判定
    foreach ($stack as $id) {
      $actor = DB::$USER->ByID($id);
      if ($actor->IsDead(true)) continue; //直前に死んでいたら無効

      $filter = RoleManager::LoadMain($actor);
      if ($ignore = $filter->IgnoreGuard()) continue; //個別護衛失敗判定
      if (! $result) {
	$result |= ! ($half && Lottery::Bool()) && (! $limited || is_null($ignore));
      }

      $filter->GuardAction(); //護衛実行処理
      //護衛成功メッセージを登録
      $this->AddSuccess($actor->id, 'guard_success'); //成功者を登録
      if (! DB::$ROOM->IsOption('seal_message') && $actor->IsFirstGuardSuccess($user->id)) {
	DB::$ROOM->ResultAbility($this->result, 'success', $user->GetName(), $actor->id);
      }
    }
    return $result && ! $user->IsRole('penetration');
  }

  //護衛者検出
  final public function GetGuard($id) {
    return array_keys($this->GetStack('guard'), $id);
  }

  //護衛制限判定
  private function IsGuardLimited(User $user) {
    if ($user->IsRoleGroup('priest')) { //司祭系
      return ! $user->IsRole('crisis_priest', 'widow_priest', 'revive_priest');
    }
    if ($user->IsMainGroup('assassin')) return true; //暗殺者系
    if ($user->IsRole('sacrifice_common', 'doll_master')) return true; //身代わり能力者

    //上位能力者
    return $user->IsRole(
      'prince', 'step_mage', 'emissary_necromancer', 'reporter', 'detective_common',
      'spell_common', 'clairvoyance_scanner', 'soul_wizard', 'barrier_wizard', 'pierrot_wizard');
  }

  //護衛失敗判定
  public function IgnoreGuard() {
    return false;
  }

  //護衛処理
  public function GuardAction() {}

  //狩り
  final public function Hunt(User $user) {
    if (! $this->hunt || $this->IgnoreHunt($user) || ! $this->IsHunt($user)) return;
    DB::$USER->Kill($user->id, 'HUNTED');
    if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
      DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $user->GetName(), $this->GetID());
    }
  }

  //狩りスキップ判定
  protected function IgnoreHunt(User $user) {
    //対象が身代わり死していた場合はスキップ
    return in_array($user->uname, $this->GetStack('sacrifice')) || $user->IsAvoidLovers(true);
  }

  //狩り対象判定
  protected function IsHunt(User $user) {
    if ($this->IsAddHunt($user)) return true; //追加狩り対象
    if ($user->IsMainGroup('mad')) { //特殊狂人
      return ! $user->IsRole(
        'mad', 'fanatic_mad', 'whisper_mad', 'swindle_mad', 'step_mad',
	'therian_mad', 'revive_mad', 'immolate_mad');
    }
    if ($user->IsRole('possessed_fox')) { //憑狐 (憑依前)
      return count($user->GetPartner('possessed_target', true)) < 1;
    }

    //特殊人外
    return $user->IsRole(
      'phantom_fox', 'voodoo_fox', 'revive_fox', 'doom_fox', 'trap_fox', 'cursed_fox',
      'cursed_angel', 'incubus_vampire', 'succubus_vampire', 'doom_vampire', 'sacrifice_vampire',
      'soul_vampire', 'poison_chiroptera', 'cursed_chiroptera', 'boss_chiroptera', 'cursed_avenger',
      'critical_avenger');
  }

  //追加狩り対象判定
  protected function IsAddHunt(User $user) {
    return false;
  }
}
