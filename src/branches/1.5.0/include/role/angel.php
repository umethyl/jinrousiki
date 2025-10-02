<?php
/*
  ◆天使 (angel)
  ○仕様
  ・共感者判定：男女
*/
RoleManager::LoadFile('cupid');
class Role_angel extends Role_cupid{
  function __construct(){ parent::__construct(); }

  function VoteNightAction($list, $flag){
    global $ROOM;

    parent::VoteNightAction($list, $flag);
    $lovers_a = $list[0];
    $lovers_b = $list[1];
    if(! $this->IsSympathy($lovers_a, $lovers_b)) return; //共感者判定

    $lovers_a->AddRole('mind_sympathy');
    $sentence = $lovers_a->handle_name . "\t" . $lovers_b->handle_name . "\t";
    $ROOM->SystemMessage($sentence . $lovers_b->main_role, 'SYMPATHY_RESULT');

    $lovers_b->AddRole('mind_sympathy');
    $sentence = $lovers_b->handle_name . "\t" . $lovers_a->handle_name . "\t";
    $ROOM->SystemMessage($sentence . $lovers_a->main_role, 'SYMPATHY_RESULT');
  }

  protected function IsSympathy($lovers_a, $lovers_b){ return $lovers_a->sex != $lovers_b->sex; }
}
