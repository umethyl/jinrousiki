<?php
/*
  ◆厄神 (anti_voodoo)
  ○仕様
  ・能力結果：厄払い (天啓封印あり)
*/
class Role_anti_voodoo extends Role {
  public $action = VoteAction::ANTI_VOODOO;
  public $result = RoleAbility::ANTI_VOODOO;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message');
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::GUARD, RoleAbilityMessage::ANTI_VOODOO, $this->action);
  }

  //厄払い先セット (憑依妨害 > 憑依者(強制送還) > 襲撃憑狼(襲撃キャンセル))
  final public function SetVoodooGuard(User $user) {
    $this->AddStack($user->id);
    $stack = $this->GetStackKey(RoleVoteSuccess::POSSESSED, $user->id);
    $wolf  = $this->GetWolfVoter();
    if (count($stack) > 0) {
      foreach ($stack as $id) {
	DB::$USER->ByID($id)->Flag()->On(UserMode::POSSESSED_CANCEL);
      }
    } elseif (RoleUser::IsPossessed($user) && false === $user->IsSame($user->GetVirtual())) {
      if (false === RoleUser::IsPossessedTarget($user)) {
	$this->AddSuccess($user->id, RoleVoteSuccess::POSSESSED, true); //憑依リストに追加
      }
      $user->Flag()->On(UserMode::POSSESSED_RESET);
    } elseif ($wolf->IsRole('possessed_wolf') && $wolf->IsSame($user)) {
      $wolf->Flag()->On(UserMode::POSSESSED_CANCEL);
    } else {
      return;
    }
    $this->AddSuccess($user->id, RoleVoteSuccess::ANTI_VOODOO);
  }

  //厄払い成立判定
  public function IsGuard($id) {
    if (! $this->InStack($id)) {
      return false;
    }

    $this->AddSuccess($id, RoleVoteSuccess::ANTI_VOODOO);
    return true;
  }

  //成功結果登録
  public function SaveSuccess() {
    foreach ($this->GetStack(RoleVoteSuccess::ANTI_VOODOO) as $target_id => $flag) {
      $target = DB::$USER->ByVirtual($target_id)->handle_name;
      foreach ($this->GetStackKey($this->role, $target_id) as $id) { //成功者を検出
	DB::$ROOM->StoreAbility($this->result, 'success', $target, $id);
      }
    }
  }
}
