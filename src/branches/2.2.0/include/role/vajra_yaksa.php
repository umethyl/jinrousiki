<?php
/*
  ◆金剛夜叉 (vajra_yaksa)
  ○仕様
  ・勝利：生存 + 蘇生能力者全滅 + 村人陣営以外勝利
*/
RoleManager::LoadFile('yaksa');
class Role_vajra_yaksa extends Role_yaksa {
  public $reduce_rate = 3;

  protected function IgnoreWin($winner) { return $winner == 'human'; }

  protected function IgnoreAssassin(User $user) {
    return ! ($user->IsMainGroup('poison_cat') || $user->IsRoleGroup('revive') ||
	      $user->IsRole('scarlet_vampire', 'resurrect_mania'));
  }
}
