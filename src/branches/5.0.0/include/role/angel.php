<?php
/*
  ◆天使 (angel)
  ○仕様
  ・共感者判定：男女
*/
RoleLoader::LoadFile('cupid');
class Role_angel extends Role_cupid {
  protected function VoteNightCupidAction() {
    //共感者判定
    $list = $this->GetStack('target_list');
    $a = array_shift($list);
    $b = array_shift($list);
    if ($this->IsSympathy($a, $b)) {
      $this->SetSympathy($a, $b);
    }
  }

  //共感者判定
  protected function IsSympathy(User $a, User $b) {
    return ! Sex::IsSame($a, $b);
  }

  //共感者付加処理
  final protected function SetSympathy(User $a, User $b) {
    $result = RoleAbility::SYMPATHY;
    $a->AddRole('mind_sympathy');
    $b->AddRole('mind_sympathy');
    DB::$ROOM->StoreAbility($result, $b->main_role, $b->handle_name, $a->id);
    DB::$ROOM->StoreAbility($result, $a->main_role, $a->handle_name, $b->id);
  }
}
