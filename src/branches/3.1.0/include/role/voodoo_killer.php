<?php
/*
  ◆陰陽師 (voodoo_killer)
  ○仕様
  ・能力結果：解呪 (天啓封印あり)
  ・占い：解呪
*/
class Role_voodoo_killer extends Role {
  public $action = VoteAction::VOODOO_KILLER;
  public $result = RoleAbility::VOODOO_KILLER;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2 || DB::$ROOM->IsOption('seal_message');
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::MAGE, RoleAbilityMessage::VOODOO_KILLER, $this->action);
  }

  //解呪
  final public function MageVoodoo(User $user) {
    //-- 呪殺判定 (呪い所持者・憑依能力者) --//
    if ($user->IsLive(true) && ! RoleUser::IsAvoidLovers($user, true) &&
	($user->IsRoleGroup('cursed') || RoleUser::IsPossessed($user))) {
      DB::$USER->Kill($user->id, DeadReason::CURSED);
      $this->AddSuccess($user->id, RoleVoteSuccess::VOODOO_KILLER);
    }

    //-- 憑依妨害判定 --//
    $stack = $this->GetStackKey(RoleVoteSuccess::POSSESSED, $user->id);
    if (count($stack) > 0) {
      foreach ($stack as $id) {
	DB::$USER->ByID($id)->Flag()->On(UserMode::POSSESSED_CANCEL);
      }
      $this->AddSuccess($user->id, RoleVoteSuccess::VOODOO_KILLER);
    }

    //-- 解呪対象リスト追加 --//
    $this->AddStack($user->id);
  }

  //成功結果登録
  public function SaveSuccess() {
    foreach ($this->GetStack(RoleVoteSuccess::VOODOO_KILLER) as $target_id => $flag) {
      $target = DB::$USER->ByVirtual($target_id)->handle_name;
      foreach ($this->GetStackKey($this->role, $target_id) as $id) { //成功者を検出
	DB::$ROOM->ResultAbility($this->result, 'success', $target, $id);
      }
    }
  }
}
