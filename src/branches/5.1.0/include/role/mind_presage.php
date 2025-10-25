<?php
/*
  ◆受託者 (mind_presage)
  ○仕様
  ・表示：3 日目以降 (付加後の人狼襲撃後)
  ・役職表示：無し
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_presage extends Role_mind_read {
  public $result = RoleAbility::PRESAGE;

  protected function IgnoreAbility() {
    return DateBorder::PreThree();
  }

  protected function IgnoreImage() {
    return true;
  }
}
