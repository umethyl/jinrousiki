<?php
/*
  ◆鬼 (ogre)
  ○仕様
  ・勝利：生存 + 人狼系の生存
  ・人攫い成功率低下：1/5
  ・人狼襲撃：確率無効
  ・人狼襲撃無効確率：30%
  ・暗殺反射確率：30%
*/
class Role_ogre extends Role {
  public $action     = VoteAction::OGRE;
  public $not_action = VoteAction::NOT_OGRE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::OGRE;
    RoleHTML::OutputVoteNight(VoteCSS::OGRE, $str, $this->action, $this->not_action);
  }

  protected function DisableNotAction() {
    return DB::$ROOM->IsEvent('force_assassin_do');
  }

  final public function ResistWolfEat() {
    $event = $this->GetOgreEvent();
    $rate  = is_null($event) ? $this->GetOgreResistWolfEatRate() : $event;
    //Text::p($rate, '◆Resist Rate [ogre]');
    return Lottery::Percent($rate);
  }

  //鬼陣営天候情報取得 (朧月 > 叢雲)
  final protected function GetOgreEvent() {
    if (DB::$ROOM->IsEvent('full_ogre')) {
      return 100;
    } elseif (DB::$ROOM->IsEvent('seal_ogre')) {
      return   0;
    } else {
      return null;
    }
  }

  //鬼陣営人狼襲撃無効確率取得
  protected function GetOgreResistWolfEatRate() {
    return 30;
  }

  //暗殺反射確率取得
  public function GetReflectAssassinRate() {
    return 30;
  }

  //人攫い情報セット
  //罠 > 対暗殺護衛 > 死亡 > 逃亡 > 反射 > 個別無効 > 人攫い成功判定 > 通常 → 更新判定
  final public function SetOgreAssassin(User $user) {
    if (RoleUser::DelayTrap($this->GetActor(), $user->id)) {
      return false;
    } elseif (RoleUser::GuardAssassin($user)) {
      return false;
    } elseif ($user->IsDead(true)) {
      return false;
    } elseif (RoleUser::IsEscape($user)) {
      return false;
    } elseif (RoleUser::IsReflectAssassin($user)) {
      $this->AddSuccess($this->GetID(), RoleVoteSuccess::OGRE);
      return false;
    } elseif ($this->IgnoreSetOgreAssassin($user)) {
      return false;
    } else {
      $count = (int)$this->GetActor()->GetMainRoleTarget();
      $event = $this->GetOgreEvent();
      if (is_null($event)) {
	$reduce_rate = $this->GetOgreReduceNumerator() / $this->GetOgreReduceDenominator();
	$rate = ceil(100 * pow($reduce_rate, $count));
      } else {
	$rate = $event;
      }
      //Text::p($rate, '◆AssassinRate [ogre]');

      if (false === Lottery::Percent($rate)) { //成功判定
	return false;
      }
    }

    $this->OgreAssassin($user);
    if (false === DB::$ROOM->IsEvent('full_ogre')) { //成功回数更新処理 (朧月ならスキップ)
      $role = $this->role;
      if ($count > 0) {
	$role .= sprintf('[%d]', $count);
      }
      $this->GetActor()->ReplaceRole($role, sprintf('%s[%d]', $this->role, $count + 1));
    }
    return true;
  }

  //人攫い情報セット失敗判定
  protected function IgnoreSetOgreAssassin(User $user) {
    return false;
  }

  //人攫い成功率低下 (分子)
  protected function GetOgreReduceNumerator() {
    return 1;
  }

  //人攫い成功率低下 (分母)
  protected function GetOgreReduceDenominator() {
    return 5;
  }

  //人攫い
  protected function OgreAssassin(User $user) {
    $this->AddSuccess($user->id, RoleVoteSuccess::OGRE);
  }

  //人攫い死亡処理
  final public function OgreAssassinKill() {
    foreach ($this->GetStack() as $id => $flag) {
      DB::$USER->Kill($id, DeadReason::OGRE_KILLED);
    }
  }

  public function Win($winner) {
    //勝利確定陣営 > 敗北確定陣営 > 生存 > 敗北確定生存者 > 敗北確定全滅者 > 個別
    if ($this->IsOgreWinCamp($winner)) {
      return true;
    } elseif ($this->IsOgreLoseCamp($winner)) {
      return false;
    } elseif ($this->IsOgreLoseLive()) {
      return false;
    } elseif ($this->IsOgreLoseSurvive()) {
      return false;
    } elseif ($this->IsOgreLoseAllDead()) {
      return false;
    } else {
      return $this->OgreWin();
    }
  }

  //鬼陣営勝敗判定 (勝利確定陣営)
  /* 鬼は人狼陣営勝利で実質勝利確定となるが、基本種であることも加味してここでは判定を入れない */
  protected function IsOgreWinCamp($winner) {
    return false;
  }

  //鬼陣営勝敗判定 (敗北確定陣営)
  protected function IsOgreLoseCamp($winner) {
    return false;
  }

  //鬼陣営勝敗判定 (生存)
  protected function IsOgreLoseLive() {
    return $this->IsActorDead();
  }

  //鬼陣営勝敗判定 (敗北確定生存者)
  final protected function IsOgreLoseSurvive() {
    if ($this->IgnoreOgreLoseSurvive()) {
      return false;
    }

    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $this->RequireOgreWinDead($user)) {
	return true;
      }
    }
    return false;
  }

  //鬼陣営勝敗判定スキップ (敗北確定生存者)
  protected function IgnoreOgreLoseSurvive() {
    return true;
  }

  //鬼陣営勝敗判定対象者 (敗北確定生存者)
  protected function RequireOgreWinDead(User $user) {
    return false;
  }

  //鬼陣営勝敗判定 (敗北確定全滅者)
  final protected function IsOgreLoseAllDead() {
    if ($this->IgnoreOgreLoseAllDead()) {
      return false;
    }

    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive() && $this->RequireOgreWinSurvive($user)) {
	return false;
      }
    }
    return true;
  }

  //鬼陣営勝敗判定スキップ (敗北確定全滅者)
  protected function IgnoreOgreLoseAllDead() {
    return false;
  }

  //鬼陣営勝敗判定対象者 (敗北確定全滅者)
  protected function RequireOgreWinSurvive(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF);
  }

  //鬼陣営勝敗判定 (鬼陣営個別)
  protected function OgreWin() {
    return true;
  }
}
