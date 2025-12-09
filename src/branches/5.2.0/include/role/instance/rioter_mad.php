<?php
/*
  ◆暴徒 (rioter_mad)
  ○仕様
  ・暴動登録：1回限り
  ・死者妨害：暴動死 (蘇生者の周囲からランダム2人)
*/
class Role_rioter_mad extends Role {
  public $action     = VoteAction::RIOTE;
  public $not_action = VoteAction::NOT_RIOTE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return $this->IsActorActive();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::RIOTE;
    RoleHTML::OutputVoteNight(VoteCSS::WOLF, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function FixLiveVoteNightIconPath() {
    return true;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  //暴動登録 (罠死 > 暴動登録処理)
  public function SetRiote(User $user) {
    if ($this->InStack($user->id, RoleVoteTarget::TRAP)) {
      DB::$USER->Kill($this->GetID(), DeadReason::TRAPPED);
    } else {
      if ($this->InStack($user->id, RoleVoteTarget::SNOW_TRAP)) { //凍傷判定
	$this->AddStack($this->GetID(), RoleVoteSuccess::FROSTBITE);
      }
      $this->AddStack($user->id, RoleVoteTarget::RIOTE);
      $this->GetActor()->LostAbility();
    }
  }

  //暴動
  public function Riote() {
    //暴動発動リストを取得
    $riote_list = $this->GetRioteTarget();
    if (count($riote_list) < 1) {
      return;
    }

    //処理順で死体の発生数が変動するのでランダム化
    $riote_order_list = array_keys($riote_list);
    shuffle($riote_order_list);
    //Text::p($riote_order_list, "◆Riote/Order [$this->role]");
    foreach ($riote_order_list as $id) {
      $this->RioteKill(DB::$USER->ByID($id), count($riote_list[$id]));
    }
  }

  //蘇生者と投票した暴徒の情報収集
  private function GetRioteTarget() {
    $stack = [];
    foreach ($this->GetStack(RoleVoteTarget::RIOTE) as $id => $target_id) {
      //死亡している場合は能力無効 (蘇生していても無効)
      if (DB::$USER->ByID($id)->IsOn(UserMode::DEAD)) {
	continue;
      }

      if (DB::$USER->ByID($target_id)->IsOn(UserMode::REVIVE)) {
	$stack[$target_id][] = $id;
      }
    }
    //Text::p($stack, "◆Riote/Target [$this->role]");
    return $stack;
  }

  //暴動死処理
  private function RioteKill(User $user, int $role_count) {
    $stack = $this->GetRioteKillTarget($user);
    shuffle($stack);

    //暴動死の最大数を決定
    $dead_count = min(count($stack), $role_count * 2);
    for ($i = 0; $i < $dead_count; $i++) {
      DB::$USER->Kill(array_pop($stack), DeadReason::RIOTED);
    }
  }

  //暴動死の候補者をリストアップ (蘇生者・死者・特殊耐性は除外)
  private function GetRioteKillTarget(User $user) {
    $stack = [];
    foreach (Position::GetAround($user) as $id) {
      if (true !== DB::$USER->IsVirtualLive($id, true)) {
	continue;
      }

      $target = DB::$USER->ByID($id);
      if ($target->IsOff(UserMode::REVIVE) && true !== RoleUser::Avoid($target)) {
	$stack[] = $id;
      }
    }
    //Text::p($stack, "◆RioteKill/Target [$this->role]");
    return $stack;
  }
}
