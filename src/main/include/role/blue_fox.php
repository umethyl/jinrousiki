<?php
/*
  ◆蒼狐 (blue_fox)
  ○仕様
  ・人狼襲撃カウンター：はぐれ者
*/
RoleLoader::LoadFile('fox');
class Role_blue_fox extends Role_fox {
  public function WolfEatFoxCounter(User $user) {
    if (false === RoleUser::IsLonely($user)) {
      $user->AddRole('mind_lonely');
    }
  }
}
