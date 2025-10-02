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

  protected function OutputResult() {
    if (DB::$ROOM->date > 1) $this->OutputAbilityResult($this->result);
  }

  function OutputAction() {
    RoleHTML::OutputVote('mage-do', 'mage_do', $this->action);
  }

  //占い
  function Mage(User $user) {
    if ($this->IsJammer($user)) {
      return $this->SaveMageResult($user, $this->mage_failed, $this->result);
    }
    if ($this->IsCursed($user)) return false;
    $this->SaveMageResult($user, $this->GetMageResult($user), $this->result);
  }

  //占い失敗判定
  function IsJammer(User $user) {
    $uname   = $this->GetUname();
    $half    = DB::$ROOM->IsEvent('half_moon') && mt_rand(0, 1) > 0; //半月
    $phantom = $user->IsLive(true) && $user->IsRoleGroup('phantom') && $user->IsActive(); //幻系

    if ($half || $phantom) {
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($uname)) return false;
      }
    }

    if ($half || in_array($uname, $this->GetStack('jammer'))) return true; //占い妨害判定
    if ($phantom) { //幻系判定
      $this->AddSuccess($user->user_no, 'phantom');
      return true;
    }
    return false;
  }

  //呪返し判定
  function IsCursed(User $user) {
    if ($user->IsCursed() || in_array($user->uname, $this->GetStack('voodoo'))) {
      $actor = $this->GetActor();
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->uname)) return false;
      }
      DB::$USER->Kill($actor->user_no, 'CURSED');
      return true;
    }
    return false;
  }

  //厄払いフィルタ取得
  protected function GetGuardCurse() {
    if (! is_array($stack = $this->GetStack($data = 'guard_curse'))) {
      $stack = RoleManager::LoadFilter($data);
      $this->SetStack($stack, $data);
    }
    return $stack;
  }

  //占い結果取得
  function GetMageResult(User $user) {
    if (array_key_exists($user->uname, $this->GetStack('possessed'))) { //憑依キャンセル判定
      $user->possessed_cancel = true;
    }

    //呪殺判定
    if ($user->IsLive(true) && ! DB::$ROOM->IsEvent('no_fox_dead') &&
	(($user->IsFox() && ! $user->IsChildFox() &&
	  ! $user->IsRole('white_fox', 'black_fox', 'mist_fox', 'sacrifice_fox')) ||
	 $user->IsRoleGroup('spell'))) {
      DB::$USER->Kill($user->user_no, 'FOX_DEAD');
    }
    return $this->DistinguishMage($user); //占い判定
  }

  //占い判定
  function DistinguishMage(User $user, $reverse = false) {
    //鬼火系判定
    if ($user->IsDoomRole('sheep_wisp')) return $reverse ? 'wolf' : 'human';
    if ($user->IsRole('wisp'))           return 'ogre';
    if ($user->IsRole('foughten_wisp'))  return 'chiroptera';
    if ($user->IsRole('black_wisp'))     return $reverse ? 'human' : 'wolf' ;

    //特殊役職判定
    if ($user->IsOgre()) return 'ogre';
    if ($user->IsRoleGroup('vampire', 'mist') || $user->IsRole('boss_chiroptera')) {
      return 'chiroptera';
    }

    //人狼判定
    $flag = ($user->IsWolf() && ! $user->IsRole('boss_wolf') && ! $user->IsSiriusWolf()) ||
      $user->IsRole('suspect', 'cute_mage', 'swindle_mad', 'black_fox', 'cute_chiroptera',
		    'cute_avenger');
    return ($flag xor $reverse) ? 'wolf' : 'human';
  }

  //占い結果登録
  function SaveMageResult(User $user, $result, $action) {
    $target = DB::$USER->GetHandleName($user->uname, true);
    DB::$ROOM->ResultAbility($action, $result, $target, $this->GetID());
  }
}
