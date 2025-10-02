<?php
/*
  ◆鬼
  ○仕様
  ・勝利条件：自分自身と人狼系の生存
*/
class Role_ogre extends Role{
  var $resist_rate = 30;

  function Role_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS;

    if($this->IsDead()) return false;
    if($victory == 'wolf') return true;
    foreach($USERS->rows as $user){
      if($user->IsLiveRoleGroup('wolf')) return true;
    }
    return false;
  }
}
