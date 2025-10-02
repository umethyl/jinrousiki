<?php
/*
  ◆ひよこ鑑定士 (sex_mage)
  ○仕様
  ・占い：性別鑑定
*/
RoleManager::LoadFile('psycho_mage');
class Role_sex_mage extends Role_psycho_mage {
  protected function GetMageResult(User $user) {
    return $this->DistinguishSex($user);
  }

  //性別鑑定
  final public function DistinguishSex(User $user) {
    if ($user->IsMainCamp('ogre')) {
      return 'ogre';
    } elseif ($user->IsMainCamp('chiroptera') || $user->IsRoleGroup('gold')) {
      return 'chiroptera';
    } else {
      return 'sex_' . $user->sex;
    }
  }
}
