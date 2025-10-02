<?php
/*
  ◆呪術師 (voodoo_mad)
  ○仕様
*/
class Role_voodoo_mad extends Role {
  public $action = VoteAction::VOODOO_MAD;

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::WOLF, RoleAbilityMessage::VOODOO, $this->action);
  }

  //呪術対象セット
  final public function SetVoodoo(User $user) {
    if (RoleUser::IsCursed($user)) { //呪返し判定
      RoleUser::GuardCurse($this->GetActor()); //厄払い判定
      return false;
    }

    if ($this->InStack($user->id, 'voodoo_killer')) { //陰陽師の解呪判定
      $this->AddSuccess($user->id, RoleVoteSuccess::VOODOO_KILLER);
    } else {
      $this->AddStack($user->id, 'voodoo');
    }
  }

  //呪術能力者の呪返し処理
  final public function VoodooToVoodoo() {
    $stack      = $this->GetStack('voodoo');
    $count_list = array_count_values($stack);
    foreach ($stack as $id => $target_id) {
      if ($count_list[$target_id] < 2) continue;
      RoleUser::GuardCurse(DB::$USER->ByID($id)); //厄払い判定
    }
  }
}
