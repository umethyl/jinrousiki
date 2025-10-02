<?php
/*
  ◆蒼狼
  ○仕様
  ・妖狐襲撃：はぐれ者
*/
RoleLoader::LoadFile('wolf');
class Role_blue_wolf extends Role_wolf {
  protected function WolfEatFoxAction(User $user) {
    if (false === RoleUser::IsLonely($user)) {
      $user->AddRole('mind_lonely');
    }
  }
}
