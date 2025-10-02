<?php
/*
  ◆夜叉丸 (betray_yaksa)
  ○仕様
  ・勝利：生存 + 蝙蝠陣営全滅 + 村人陣営勝利
  ・人攫い無効：蝙蝠陣営以外
*/
RoleManager::LoadFile('yaksa');
class Role_betray_yaksa extends Role_yaksa {
  protected function IgnoreWin($winner) { return $winner != 'human'; }

  protected function IgnoreAssassin(User $user) { return ! $user->IsCamp('chiroptera', true); }
}
