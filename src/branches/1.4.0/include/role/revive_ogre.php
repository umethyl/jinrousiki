<?php
/*
  ◆茨木童子
  ○仕様
  ・勝利条件：自分自身の生存 + 嘘吐きの全滅
*/
class Role_revive_ogre extends Role{
  var $resist_rate = 0;

  function Role_revive_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS;

    if($this->IsDead()) return false;
    foreach($USERS->rows as $user){
      if($user->IsLive() && $user->DistinguishLiar() == 'psycho_mage_liar') return false;
    }
    return true;
  }
}
