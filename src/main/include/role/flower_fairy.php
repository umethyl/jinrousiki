<?php
/*
  ◆花妖精 (flower_fairy)
  ○仕様
  ・悪戯：死亡欄妨害 (花)
*/
RoleLoader::LoadFile('fairy');
class Role_flower_fairy extends Role_fairy {
  protected function FairyAction(User $user) {
    $result = $this->CallParent('GetFairyActionResult');
    DB::$ROOM->StoreDead($user->GetName(), $result, Lottery::GetRange('A', 'Z'));
  }

  //死亡欄妨害種別取得
  protected function GetFairyActionResult() {
    return DeadReason::FLOWERED;
  }

  //悪戯 (天候)
  public function FairyEvent() {
    $this->FairyAction(DB::$USER->ByID(Lottery::Get(array_keys(DB::$USER->SearchLive()))));
  }
}
