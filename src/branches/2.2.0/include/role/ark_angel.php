<?php
/*
  ◆大天使 (ark_angel)
  ○仕様
  ・結果表示：共感者
  ・共感者判定：無効
*/
RoleManager::LoadFile('angel');
class Role_ark_angel extends Role_angel {
  protected function OutputResult() {
    if (DB::$ROOM->IsDate(2)) $this->OutputAbilityResult('SYMPATHY_RESULT');
  }

  protected function IsSympathy(User $a, User $b) { return false; }
}
