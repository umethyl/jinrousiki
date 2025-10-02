<?php
/*
  ◆天使 (angel)
  ○仕様
  ・共感者判定：男女
*/
RoleManager::LoadFile('cupid');
class Role_angel extends Role_cupid {
  function VoteNightAction(array $list, $flag) {
    parent::VoteNightAction($list, $flag);
    //共感者判定
    $a = array_shift($list);
    $b = array_shift($list);
    if ($this->IsSympathy($a, $b)) $this->SetSympathy($a, $b);
  }

  protected function IsSympathy(User $a, User $b) { return $a->sex != $b->sex; }

  //共感者処理
  protected function SetSympathy(User $a, User $b) {
    $action = 'SYMPATHY_RESULT';
    $a->AddRole('mind_sympathy');
    $b->AddRole('mind_sympathy');
    DB::$ROOM->ResultAbility($action, $b->main_role, $b->handle_name, $a->user_no);
    DB::$ROOM->ResultAbility($action, $a->main_role, $a->handle_name, $b->user_no);
  }
}
