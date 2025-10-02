<?php
/*
  ◆熱病 (febris)
  ○仕様
  ・発動当日ならショック死する
*/
class Role_febris extends Role{
  function Role_febris(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES, $ROOM;
    if($reason == '' && $ROOM->date == $ROLES->actor->GetDoomDate('febris')) $reason = 'FEBRIS';
  }
}
