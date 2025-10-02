<?php
/*
  ◆陰陽師 (voodoo_killer)
  ○仕様
  ・占い：解呪
*/
class Role_voodoo_killer extends Role {
  public $action = 'VOODOO_KILLER_DO';
  public $result = 'VOODOO_KILLER_SUCCESS';

  protected function OutputResult() {
    if (DB::$ROOM->date > 1 && ! DB::$ROOM->IsOption('seal_message')) {
      $this->OutputAbilityResult($this->result);
    }
  }

  function OutputAction() {
    RoleHTML::OutputVote('mage-do', 'voodoo_killer_do', $this->action);
  }

  //占い
  function Mage(User $user) {
    //呪殺判定 (呪い所持者・憑依能力者)
    if ($user->IsLive(true) && ($user->IsRoleGroup('cursed') || $user->IsPossessedGroup())) {
      DB::$USER->Kill($user->id, 'CURSED');
      $this->AddSuccess($user->id, $this->role . '_success');
    }
    if (count($stack = array_keys($this->GetStack('possessed'), $user->id)) > 0) { //憑依妨害判定
      foreach ($stack as $id) {
	DB::$USER->ByID($id)->possessed_cancel = true;
      }
      $this->AddSuccess($user->id, $this->role . '_success');
    }
    $this->AddStack($user->id); //解呪対象リストに追加
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
