<?php
/*
  ◆白蔵主 (sacrifice_fox)
  ○仕様
  ・人狼襲撃耐性：身代わり
  ・身代わり：子狐系・蝙蝠系
*/
RoleLoader::LoadFile('fox');
class Role_sacrifice_fox extends Role_fox {
  public $mix_in = ['protected'];

  public function ResistWolfEatFox() {
    return false;
  }

  protected function IsSacrifice(User $user) {
    return $user->IsMainGroup(CampGroup::CHILD_FOX, CampGroup::CHIROPTERA);
  }
}
