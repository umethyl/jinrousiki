<?php
/*
  ◆死の宣告 (death_warrant)
  ○仕様
  ・発動当日ならショック死する
*/
class Role_death_warrant extends Role{
  function Role_death_warrant(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES, $ROOM;
    if($reason == '' && $ROOM->date == $ROLES->actor->GetDoomDate('death_warrant')){
      $reason = 'WARRANT';
    }
  }
}
