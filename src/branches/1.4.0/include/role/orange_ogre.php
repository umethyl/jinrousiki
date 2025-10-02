<?php
/*
  ◆前鬼
  ○仕様
  ・勝利条件：自分自身の生存 + 人狼陣営の全滅
*/
class Role_orange_ogre extends Role{
  var $resist_rate = 30;

  function Role_orange_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS;

    if($this->IsDead() || $victory == 'wolf') return false;
    foreach($USERS->rows as $user){
      if($user->IsLive() && $user->GetCamp(true) == 'wolf') return false;
    }
    return true;
  }
}
