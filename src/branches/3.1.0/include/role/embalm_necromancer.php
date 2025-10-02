<?php
/*
  ◆死化粧師 (embalm_necromancer)
  ○仕様
  ・霊能：処刑投票先との陣営比較
*/
RoleLoader::LoadFile('necromancer');
class Role_embalm_necromancer extends Role_necromancer {
  public $result = RoleAbility::EMBALM_NECROMANCER;

  public function Necromancer(User $user, $flag) {
    if ($flag) {
      return 'stolen';
    } else {
      $camp = $this->GetVoteUser($user->uname)->GetWinCamp();
      return Text::AddFooter('embalm', $user->IsWinCamp($camp) ? 'agony' : 'reposeful');
    }
  }
}
