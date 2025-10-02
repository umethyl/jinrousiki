<?php
/*
  ◆ひよこ鑑定士 (sex_mage)
  ○仕様
  ・占い：性別鑑定
*/
RoleLoader::LoadFile('psycho_mage');
class Role_sex_mage extends Role_psycho_mage {
  protected function GetMageResult(User $user) {
    return $this->DistinguishSex($user);
  }

  //性別鑑定 (サブ・金系 > 陣営 > 通常)
  final public function DistinguishSex(User $user) {
    if ($user->IsRoleGroup('gold')) {
      return Camp::CHIROPTERA;
    } elseif ($user->IsMainCamp(Camp::CHIROPTERA) || $user->IsMainCamp(Camp::OGRE)) {
      return $user->DistinguishCamp();
    } else {
      return Sex::Distinguish($user);
    }
  }
}
