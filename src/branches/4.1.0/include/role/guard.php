<?php
/*
  ◆狩人 (guard)
  ○仕様
  ・能力結果：護衛成功・狩り (天啓封印あり)
  ・護衛失敗：なし
  ・護衛制限：あり
  ・護衛処理：なし
  ・護衛成功登録：あり
  ・狩り：通常
*/
class Role_guard extends Role {
  public $action = VoteAction::GUARD;
  public $result = RoleAbility::GUARD;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message');
  }

  protected function OutputAddResult() {
    if (false === $this->IgnoreHunt()) {
      RoleHTML::OutputResult(RoleAbility::HUNTED);
    }
    $this->OutputGuardAddResult();
  }

  //追加結果表示 (狩人系専用)
  protected function OutputGuardAddResult() {}

  //護衛結果表示 (Mixin 用)
  final protected function OutputGuardResult() {
    if ($this->IgnoreResult()) {
      return;
    }
    RoleHTML::OutputResult($this->result);
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::GUARD, RoleAbilityMessage::GUARD, $this->action);
  }

  //護衛先セット
  final public function SetGuard(User $user) {
    if ($this->IgnoreSetGuard()) {
      return;
    }
    $this->SetGuardStack($user);
    $this->SetGuardAction($user);
  }

  //護衛先セットスキップ判定
  protected function IgnoreSetGuard() {
    return DB::$ROOM->IsEvent('no_contact');
  }

  //護衛先情報登録
  protected function SetGuardStack(User $user) {
    $this->AddStack($user->id, RoleVoteTarget::GUARD);
    RoleUser::DelayTrap($this->GetActor(), $user->id);
  }

  //護衛先セット追加処理
  protected function SetGuardAction(User $user) {}

  //護衛
  final public function Guard(User $user) {
    $stack = []; //護衛者検出
    foreach (RoleLoader::LoadFilter('guard') as $filter) {
      ArrayFilter::AddMerge($stack, $filter->GetGuard($user->id));
    }
    //Text::p($stack, sprintf('◆List [gurad/%s]', $this->GetVoter()->uname));

    $result  = false;
    $half    = DB::$ROOM->IsEvent('half_guard'); //曇天
    $limited = (false === DB::$ROOM->IsEvent('full_guard')) && $this->LimitedGuard($user);
    foreach ($stack as $id) {
      $actor = DB::$USER->ByID($id);
      if ($actor->IsDead(true)) { //直前に死んでいたら無効
	continue;
      }

      //-- 護衛成功判定 --//
      $filter = RoleLoader::LoadMain($actor);
      if ($filter->GuardFailed($user)) { //個別護衛失敗判定
	continue;
      }

      //対象者護衛成功判定 (成功成立済み > 天候 > 護衛制限無効 > 護衛制限)
      if (true === $result || (true === $half && Lottery::Bool())) {
      } elseif (false === $limited || $filter->UnlimitedGuard()) {
	$result = true;
      }

      //-- 護衛実行処理 --//
      $filter->GuardAction($user);

      //-- 護衛成功メッセージ登録 --//
      if ($filter->IgnoreGuardSuccess()) {
	continue;
      }

      $this->AddSuccess($actor->id, RoleVoteSuccess::GUARD); //成功者を登録
      if (DB::$ROOM->IsOption('seal_message')) {
	continue;
      }

      if (RoleUser::GuardSuccess($actor, $user->id)) {
	DB::$ROOM->StoreAbility($this->result, 'success', $user->GetName(), $actor->id);
      }
    }

    if ($user->IsSame($this->GetWolfTarget())) { //人狼襲撃時の護衛後処理
      foreach (RoleFilterData::$guard_finish_action as $role) {
	if (RoleManager::Stack()->Exists($role)) {
	  RoleLoader::Load($role)->GuardFinishAction();
	}
      }
    }

    return (true === $result) && (false === $user->IsRole('penetration'));
  }

  //護衛者検出
  final public function GetGuard($id) {
    return $this->GetStackKey(RoleVoteTarget::GUARD, $id);
  }

  //護衛失敗判定
  public function GuardFailed(User $user) {
    return false;
  }

  //護衛制限無効判定
  public function UnlimitedGuard() {
    return false;
  }

  //護衛処理
  public function GuardAction(User $user) {}

  //護衛成功登録スキップ判定
  public function IgnoreGuardSuccess() {
    return false;
  }

  //狩り
  final public function Hunt(User $user) {
    if ($this->CallParent('IgnoreHunt') || false === $this->IsHunt($user)) {
      return;
    }
    $this->HuntKill($user);
  }

  //狩りスキップ判定
  public function IgnoreHunt() {
    return false;
  }

  //狩り対象判定 (対象外 > 追加対象 > 特殊狂人 > 憑狐 (憑依前) > 特殊人外)
  protected function IsHunt(User $user) {
    if ($this->IgnoreHuntTarget($user)) {
      return false;
    } elseif ($this->IsAddHunt($user)) {
      return true;
    } elseif ($user->IsMainGroup(CampGroup::MAD)) {
      return false === $user->IsRole(
	'mad', 'fanatic_mad', 'whisper_mad', 'swindle_mad', 'step_mad', 'therian_mad',
	'revive_mad', 'spy_mad', 'immolate_mad'
      );
    } elseif ($user->IsRole('possessed_fox')) {
      return count($user->GetPartner('possessed_target', true)) < 1;
    } else {
      return $user->IsRole(
	'phantom_fox', 'voodoo_fox', 'revive_fox', 'doom_fox', 'trap_fox', 'cursed_fox',
	'cursed_angel', 'incubus_vampire', 'succubus_vampire', 'doom_vampire', 'sacrifice_vampire',
	'soul_vampire', 'poison_chiroptera', 'cursed_chiroptera', 'boss_chiroptera',
	'cursed_avenger', 'critical_avenger', 'soul_tengu'
      );
    }
  }

  //追加狩り対象判定
  protected function IsAddHunt(User $user) {
    return false;
  }

  //狩り処理
  protected function HuntKill(User $user) {
    DB::$USER->Kill($user->id, DeadReason::HUNTED);
    if (false === DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
      DB::$ROOM->StoreAbility(RoleAbility::HUNTED, 'hunted', $user->GetName(), $this->GetID());
    }
  }

  //護衛制限判定 (司祭系 > 暗殺者系・人形遣い系 > 上位能力者・身代わり能力者)
  private function LimitedGuard(User $user) {
    if ($user->IsRoleGroup('priest')) {
      return false === $user->IsRole('crisis_priest', 'widow_priest', 'revive_priest');
    } elseif ($user->IsMainGroup(CampGroup::ASSASSIN) || $user->IsRoleGroup('doll_master')) {
      return true;
    } else {
      return $user->IsRole(
	'prince', 'step_mage', 'emissary_necromancer', 'reporter', 'detective_common',
	'sacrifice_common', 'spell_common', 'clairvoyance_scanner', 'barrier_brownie',
	'soul_wizard', 'esper_wizard', 'pierrot_wizard', 'barrier_wizard'
      );
    }
  }

  //狩りスキップ対象者判定
  private function IgnoreHuntTarget(User $user) {
    //対象が身代わり死していた場合はスキップ
    return RoleManager::Stack()->ExistsKey(RoleVoteTarget::SACRIFICE, $user->id) ||
      RoleUser::IsAvoidLovers($user, true);
  }
}
