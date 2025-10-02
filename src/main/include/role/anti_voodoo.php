<?php
/*
  ◆厄神 (anti_voodoo)
  ○仕様
*/
class Role_anti_voodoo extends Role {
  public $action = 'ANTI_VOODOO_DO';
  public $result = 'ANTI_VOODOO_SUCCESS';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2 && ! DB::$ROOM->IsOption('seal_message')) {
      $this->OutputAbilityResult($this->result);
    }
  }

  function OutputAction() {
    RoleHTML::OutputVote('guard-do', 'anti_voodoo_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日の厄払いはできません'; }

  //厄払い先セット
  function SetGuard(User $user) {
    $this->AddStack($user->id);
    if (count($stack = array_keys($this->GetStack('possessed'), $user->id)) > 0) { //憑依妨害判定
      foreach ($stack as $id) {
	DB::$USER->ByID($id)->possessed_cancel = true;
      }
    }
    //憑依者なら強制送還
    elseif ($user->IsPossessedGroup() && ! $user->IsSame($user->GetVirtual())) {
      if (! array_key_exists($user->id, $this->GetStack('possessed'))) {
	$this->AddSuccess($user->id, 'possessed', true); //憑依リストに追加
      }
      $user->possessed_reset = true;
    }
    //襲撃を行った憑狼ならキャンセル
    elseif ($this->GetWolfVoter()->IsRole('possessed_wolf') &&
	    $this->GetWolfVoter()->IsSame($user)) {
      $this->GetWolfVoter()->possessed_cancel = true;
    }
    else {
      return;
    }
    $this->AddSuccess($user->id, $this->role . '_success');
  }

  //厄払い成立判定
  function IsGuard($id) {
    if (! in_array($id, $this->GetStack())) return false;
    $this->AddSuccess($id, $this->role . '_success');
    return true;
  }

  //成功結果登録
  final function SaveSuccess() {
    foreach ($this->GetStack($this->role . '_success') as $target_id => $flag) {
      $target = DB::$USER->ByVirtual($target_id)->handle_name;
      foreach (array_keys($this->GetStack(), $target_id) as $id) { //成功者を検出
	DB::$ROOM->ResultAbility($this->result, 'success', $target, $id);
      }
    }
  }
}
