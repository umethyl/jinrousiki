<?php
/*
  ◆奇術師 (trick_mania)
  ○仕様
  ・コピー：交換コピー
*/
RoleLoader::LoadFile('mania');
class Role_trick_mania extends Role_mania {
  protected function CopyAddAction(User $user, $role) {
    $this->TrickCopy($user, $role);
  }

  protected function GetCopiedRole() {
    return 'copied_trick';
  }

  //奇術処理
  final protected function TrickCopy(User $user, $role) {
    //スキップ判定 (コピー結果村人, 身代わり君, 特定役職, 投票実施者)
    if ($role == 'human' || $user->IsDummyBoy() || $user->IsRole('widow_priest', 'revive_priest')) {
      return;
    }

    foreach (RoleManager::GetVoteData() as $stack) { //投票実施者判定
      if (isset($stack[$user->id])) return;
    }

    $user->ReplaceRole($role, $user->DistinguishRoleGroup());
    DB::$ROOM->StoreDead($user->handle_name, DeadReason::COPIED_TRICK, $user->GetMainRole());
  }
}
