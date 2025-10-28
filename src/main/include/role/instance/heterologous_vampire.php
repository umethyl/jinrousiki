<?php
/*
  ◆夜魔 (heterologous_vampire)
  ○仕様
  ・吸血：異性以外なら性転換 + 性別鑑定
*/
RoleLoader::LoadFile('homogeneous_vampire');
class Role_heterologous_vampire extends Role_homogeneous_vampire {
  protected function IsInfectSexExchange(User $user) {
    return Sex::IsSame($user, $this->GetActor());
  }
}
