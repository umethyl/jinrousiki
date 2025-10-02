<?php
/*
  ◆占い師 (mage)
  ○仕様
  ・能力結果：占い
  ・占い：通常
*/
class Role_mage extends Role {
  public $action = VoteAction::MAGE;
  public $result = RoleAbility::MAGE;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::MAGE, RoleAbilityMessage::MAGE, $this->action);
  }

  //占い (無効 > 失敗 > 失敗固定 > 呪返し > 通常)
  public function Mage(User $user) {
    if ($this->IgnoreMage()) {
      return false;
    } elseif ($this->IsJammer($user) || $this->CallParent('IsMageFailed')) {
      return $this->CallParent('MageFailed', $user);
    } elseif ($this->IsCursed($user)) {
      return false;
    } else {
      $this->MageAction($user);
      return $this->CallParent('MageSuccess', $user);
    }
  }

  //占い無効判定
  protected function IgnoreMage() {
    return false;
  }

  //占い失敗判定 (無効 → 厄払い > 占い妨害 > 幻系 → なし)
  final public function IsJammer(User $user) {
    if ($this->IgnoreJammer()) return false; //無効判定

    //妨害要素個別判定
    $half    = DB::$ROOM->IsEvent('half_moon') && Lottery::Bool(); //半月
    $phantom = $user->IsLiveRoleGroup('phantom') && $user->IsActive(); //幻系

    if ($half || $phantom) { //厄払いスキップ判定
      if (RoleUser::GuardCurse($this->GetActor(), false)) return false;
    }

    if ($half || $this->InStack($this->GetID(), 'jammer')) { //占い妨害判定
      return true;
    } elseif ($phantom) { //幻系判定 (発動を記録)
      $this->AddSuccess($user->id, RoleVoteSuccess::PHANTOM);
      return true;
    } else {
      return false;
    }
  }

  //占い失敗無効判定
  protected function IgnoreJammer() {
    return false;
  }

  //占い失敗固定判定
  public function IsMageFailed() {
    return false;
  }

  //占い失敗処理
  public function MageFailed(User $user) {
    return $this->SaveMageResult($user, $this->GetMageFailed(), $this->result);
  }

  //占い失敗結果
  protected function GetMageFailed() {
    return 'failed';
  }

  //占い結果登録
  final public function SaveMageResult(User $user, $result, $action) {
    return DB::$ROOM->ResultAbility($action, $result, $user->GetName(), $this->GetID());
  }

  //呪返し判定 (無効 → 厄払い > 対象判定 → なし)
  final public function IsCursed(User $user) {
    if ($this->CallParent('IgnoreCursed')) {
      return false;
    } elseif (RoleUser::IsCursed($user) || $this->InStack($user->id, 'voodoo')) {
      return ! RoleUser::GuardCurse($this->GetActor());
    } else {
      return false;
    }
  }

  //呪返し無効判定
  public function IgnoreCursed() {
    return false;
  }

  //占い追加処理
  protected function MageAction(User $user) {}

  //占い成功処理
  public function MageSuccess(User $user) {
    return $this->SaveMageResult($user, $this->GetMageResult($user), $this->result);
  }

  //占い結果取得 (憑依キャンセル判定 → 呪殺対象判定 → 占い判定)
  protected function GetMageResult(User $user) {
    $this->MagePossessedCancel($user);
    if ($this->IsMageKill($user)) {
      $this->AddStack($user->id, 'mage_kill');
    }
    return $this->DistinguishMage($user);
  }

  //憑依キャンセル判定
  final protected function MagePossessedCancel(User $user) {
    if (RoleUser::IsPossessedTarget($user)) {
      $user->Flag()->On(UserMode::POSSESSED_CANCEL);
    }
  }

  //呪殺対象判定 (天候 > 生存状態 > 特殊判定 > サブ > メイン)
  final protected function IsMageKill(User $user) {
    if (DB::$ROOM->IsEvent('no_fox_dead')) {
      return false;
    } elseif ($user->IsDead(true) || RoleUser::IsAvoidLovers($user, true)) {
      return false;
    } elseif ($user->IsRoleGroup('spell')) {
      return true;
    } elseif ($user->IsMainGroup(CampGroup::FOX)) {
      return ! $user->IsRole('white_fox', 'black_fox', 'mist_fox', 'tiger_fox', 'sacrifice_fox');
    } else {
      return false;
    }
  }

  //占い判定 (妨害 > 鬼系 > 霧系 > 通常)
  final public function DistinguishMage(User $user, $reverse = false) {
    foreach (RoleFilterData::$jammer_mage_result as $role) {
      if ($user->IsRole($role)) {
	$result = RoleLoader::Load($role)->GetJammerMageResult($user, $reverse);
	if (isset($result)) return $result;
      }
    }

    if ($user->IsMainCamp(Camp::OGRE) || $user->IsRoleGroup('tiger')) {
      return 'ogre';
    } elseif ($user->IsMainGroup(CampGroup::VAMPIRE) || $user->IsRoleGroup('mist') ||
	      $user->IsRole('boss_chiroptera')) {
      return 'chiroptera';
    } else {
      return ($this->IsMageWolf($user) xor $reverse) ? 'wolf' : 'human';
    }
  }

  //占い人狼判定 (人狼系 > 個別)
  final protected function IsMageWolf(User $user) {
    if ($user->IsMainGroup(CampGroup::WOLF)) {
      return ! $user->IsRole('boss_wolf') && ! RoleUser::IsSiriusWolf($user);
    } else {
      return $user->IsRole(
	'suspect', 'cute_mage', 'swindle_mad', 'black_fox', 'cute_chiroptera', 'cute_avenger'
      );
    }
  }

  //呪殺処理
  final public function MageKill() {
    $fox_list   = array(); //妖狐カウント
    $other_list = array(); //それ以外
    foreach ($this->GetStack('mage_kill') as $id) {
      $user = DB::$USER->ByID($id);
      if (RoleUser::IsFoxCount($user)) {
	$fox_list[]   = $id;
      } else {
	$other_list[] = $id;
      }
    }
    //Text::p($fox_list,   '◆List[mage_kill/fox]');
    //Text::p($other_list, '◆List[mage_kill/other]');

    $stack = $this->GetMageKillSacrificeList(); //呪殺身代わり能力者
    foreach (Lottery::GetList($fox_list) as $id) {
      if (count($stack) > 0) {
	$id = array_pop($stack); //身代わり判定
      }
      DB::$USER->Kill($id, DeadReason::FOX_DEAD);
    }

    foreach (Lottery::GetList($other_list) as $id) {
      DB::$USER->Kill($id, DeadReason::FOX_DEAD);
    }
  }

  //呪殺身代わり能力者取得
  final protected function GetMageKillSacrificeList() {
    if (DB::$ROOM->IsEvent('no_sacrifice')) return array(); //天候判定

    $stack = array();
    foreach (RoleFilterData::$sacrifice_mage as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $target) {
	if ($target->IsLive(true) && ! RoleUser::IsAvoidLovers($target, true)) {
	  $stack[] = $target->id;
	}
      }
    }
    //Text::p($stack, '◆List[sacrifice_mage]');
    return count($stack) > 0 ? Lottery::GetList($stack) : $stack;
  }
}
