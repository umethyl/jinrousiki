<?php
/*
  ◆逃亡者 (escaper)
  ○仕様
  ・逃亡失敗：人狼系
  ・逃亡処理：なし
  ・勝利：生存
*/
class Role_escaper extends Role {
  public $action      = VoteAction::ESCAPE;
  public $action_date = RoleActionDate::AFTER;

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::ESCAPE, RoleAbilityMessage::ESCAPER, $this->action);
  }

  //逃亡 (罠死 > 逃亡失敗 > 逃亡処理)
  final public function Escape(User $user) {
    if ($this->InStack($user->id, RoleVoteTarget::TRAP)) {
      DB::$USER->Kill($this->GetID(), DeadReason::TRAPPED);
    } elseif (! DB::$ROOM->IsEvent('full_escape') && $this->EscapeFailed($user)) {
      DB::$USER->Kill($this->GetID(), DeadReason::ESCAPER_DEAD);
    } else {
      if ($this->InStack($user->id, RoleVoteTarget::SNOW_TRAP)) { //凍傷判定
	$this->AddStack($this->GetID(), RoleVoteSuccess::FROSTBITE);
      }
      $this->EscapeAction($user); //逃亡処理
      $this->AddStack($user->id, RoleVoteTarget::ESCAPER); //逃亡先をセット
    }
  }

  //逃亡失敗判定
  protected function EscapeFailed(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF);
  }

  //逃亡処理
  protected function EscapeAction(User $user) {}

  public function Win($winner) {
    $this->SetStack('escaper', 'class');
    return $this->IsActorLive();
  }
}
