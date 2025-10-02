<?php
/*
  ◆紅天使 (scarlet_angel)
  ○仕様
  ・仲間表示：無意識枠追加
  ・共感者判定：常時有効
*/
RoleLoader::LoadFile('angel');
class Role_scarlet_angel extends Role_angel {
  public $mix_in = ['wolf'];

  protected function OutputAddPartner() {
    if (! DB::$ROOM->IsNight()) return;
    $stack = $this->GetWolfPartner();
    RoleHTML::OutputPartner($stack['unconscious_list'], 'unconscious_list');
  }

  protected function IsSympathy(User $a, User $b) {
    return true;
  }
}
