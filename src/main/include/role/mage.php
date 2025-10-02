<?php
/*
  ◆占い師 (mage)
  ○仕様
  ・占い：通常
*/
class Role_mage extends Role {
  public $action = 'MAGE_DO';
  public $result = 'MAGE_RESULT';
  public $mage_failed = 'failed';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  public function OutputAction() {
    RoleHTML::OutputVote('mage-do', 'mage_do', $this->action);
  }

  //占い
  public function Mage(User $user) {
    if ($this->IsJammer($user)) {
      return $this->SaveMageResult($user, $this->mage_failed, $this->result);
    }
    if ($this->IsCursed($user)) return false;
    return $this->SaveMageResult($user, $this->GetMageResult($user), $this->result);
  }

  //占い失敗判定
  final public function IsJammer(User $user) {
    $id      = $this->GetID();
    $half    = DB::$ROOM->IsEvent('half_moon') && Lottery::Bool(); //半月
    $phantom = $user->IsLive(true) && $user->IsRoleGroup('phantom') && $user->IsActive(); //幻系

    if ($half || $phantom) {
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($id)) return false;
      }
    }

    if ($half || in_array($id, $this->GetStack('jammer'))) return true; //占い妨害判定
    if ($phantom) { //幻系判定
      $this->AddSuccess($user->id, 'phantom');
      return true;
    }
    return false;
  }

  //呪返し判定
  final public function IsCursed(User $user) {
    if ($this->IgnoreCursed()) return false;
    if ($user->IsCursed() || in_array($user->id, $this->GetStack('voodoo'))) {
      $actor = $this->GetActor();
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->id)) return false;
      }
      DB::$USER->Kill($actor->id, 'CURSED');
      return true;
    }
    return false;
  }

  //呪返し無効判定
  public function IgnoreCursed() { return false; }

  //厄払いフィルタ取得
  final protected function GetGuardCurse() {
    if (! is_array($stack = $this->GetStack($data = 'guard_curse'))) {
      $stack = RoleManager::LoadFilter($data);
      $this->SetStack($stack, $data);
    }
    return $stack;
  }

  //占い結果取得
  protected function GetMageResult(User $user) {
    if (array_key_exists($user->id, $this->GetStack('possessed'))) { //憑依キャンセル判定
      $user->possessed_cancel = true;
    }
    if ($this->IsFox($user)) $this->AddStack($user->id, 'mage_kill'); //呪殺判定
    return $this->DistinguishMage($user); //占い判定
  }

  //呪殺対象判定
  final protected function IsFox(User $user) {
    if (DB::$ROOM->IsEvent('no_fox_dead')) return false; //天候判定
    if ($user->IsDead(true) || $user->IsAvoidLovers(true)) return false; //生存判定

    if ($user->IsRoleGroup('spell')) return true; //呪殺対象者判定
    if ($user->IsMainGroup('fox')) { //妖狐系判定
      return ! $user->IsRole('white_fox', 'black_fox', 'mist_fox', 'sacrifice_fox');
    }
    return false;
  }

  //呪殺処理
  final public function MageKill() {
    $stack = array(); //呪殺身代わり能力者
    foreach (RoleFilterData::$sacrifice_mage as $role) {
      foreach (DB::$USER->GetRoleUser($role) as $target) {
	if ($target->IsLive(true) && ! $target->IsAvoidLovers(true)) {
	  $stack[] = $target->id;
	}
      }
    }
    //Text::p($stack, '◆List[sacrifice_mage]');
    if (count($stack) > 0) $stack = Lottery::GetList($stack);

    $fox_list   = array(); //妖狐カウント
    $other_list = array(); //それ以外
    foreach ($this->GetStack('mage_kill') as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsFoxCount()) {
	$fox_list[]   = $id;
      } else {
	$other_list[] = $id;
      }
    }
    //Text::p($fox_list,   '◆List[mage_kill/fox]');
    //Text::p($other_list, '◆List[mage_kill/other]');

    foreach (Lottery::GetList($fox_list) as $id) {
      if (count($stack) > 0) $id = array_pop($stack); //身代わり判定
      DB::$USER->Kill($id, 'FOX_DEAD');
    }

    foreach (Lottery::GetList($other_list) as $id) {
      DB::$USER->Kill($id, 'FOX_DEAD');
    }
  }

  //占い判定
  final public function DistinguishMage(User $user, $reverse = false) {
    //鬼火系判定
    if ($user->IsDoomRole('sheep_wisp')) return $reverse ? 'wolf' : 'human';
    if ($user->IsRole('wisp'))           return 'ogre';
    if ($user->IsRole('foughten_wisp'))  return 'chiroptera';
    if ($user->IsRole('black_wisp'))     return $reverse ? 'human' : 'wolf';

    //特殊役職判定
    if ($user->IsMainCamp('ogre')) return 'ogre';
    if ($user->IsMainGroup('vampire') || $user->IsRoleGroup('mist') ||
	$user->IsRole('boss_chiroptera')) {
      return 'chiroptera';
    }

    return ($this->IsWolf($user) xor $reverse) ? 'wolf' : 'human'; //人狼判定
  }

  //人狼判定
  final protected function IsWolf(User $user) {
    if ($user->IsMainGroup('wolf')) { //人狼系判定
      return ! $user->IsRole('boss_wolf') && ! $user->IsSiriusWolf();
    }
    return $user->IsRole('suspect', 'cute_mage', 'swindle_mad', 'black_fox',
			 'cute_chiroptera', 'cute_avenger');
  }

  //占い結果登録
  final public function SaveMageResult(User $user, $result, $action) {
    return DB::$ROOM->ResultAbility($action, $result, $user->GetName(), $this->GetID());
  }
}
