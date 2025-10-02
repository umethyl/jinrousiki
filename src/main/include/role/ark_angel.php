<?php
/*
  ◆大天使 (ark_angel)
  ○仕様
  ・結果表示：共感者
  ・共感者判定：無効
*/
RoleLoader::LoadFile('angel');
class Role_ark_angel extends Role_angel {
  public $result = RoleAbility::SYMPATHY;

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function IsSympathy(User $a, User $b) {
    return false;
  }
}
