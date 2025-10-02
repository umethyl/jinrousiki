<?php
/*
  ◆共感者 (mind_sympathy)
  ○仕様
  ・能力結果：共感
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_sympathy extends Role_mind_read {
  public $result = RoleAbility::SYMPATHY;

  protected function IgnoreResult() {
    return false === DB::$ROOM->IsDate(2);
  }
}
