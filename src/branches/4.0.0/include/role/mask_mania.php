<?php
/*
  ◆面霊気 (mask_mania)
  ○仕様
  ・追加役職：コピー先陣営別
*/
RoleLoader::LoadFile('unknown_mania');
class Role_mask_mania extends Role_unknown_mania {
  protected function CopySelfAction(User $user) {
    if ($user->IsRoleGroup('mania')) return;

    $stack = $this->GetCopySelfActionRoleList();
    $this->GetActor()->AddRole($stack[$user->DistinguishCamp()]);
  }

  //コピー追加役職候補リスト取得
  private function GetCopySelfActionRoleList() {
    return [
      Camp::HUMAN	=> 'upper_voter',
      Camp::WOLF	=> 'black_wisp',
      Camp::FOX		=> 'spell_wisp',
      Camp::LOVERS	=> 'sweet_ringing',
      Camp::QUIZ	=> 'critical_voter',
      Camp::VAMPIRE	=> 'foughten_wisp',
      Camp::CHIROPTERA	=> 'gold_wisp',
      Camp::OGRE	=> 'wisp',
      Camp::DUELIST	=> 'star',
      Camp::TENGU	=> 'tengu_spell_wisp'
    ];
  }
}
