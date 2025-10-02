<?php
/*
  ◆ひよこ鑑定士 (sex_mage)
  ○仕様
  ・占い：性別鑑定
*/
RoleManager::LoadFile('psycho_mage');
class Role_sex_mage extends Role_psycho_mage {
  function GetMageResult(User $user) { return $this->DistinguishSex($user); }

  //性別鑑定
  function DistinguishSex(User $user) {
    return $user->IsOgre() ? 'ogre' :
      ($user->IsRoleGroup('chiroptera', 'fairy', 'gold') ? 'chiroptera' : 'sex_' . $user->sex);
  }
}
