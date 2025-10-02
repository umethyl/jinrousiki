<?php
/*
  ◆後鬼
  ○仕様
  ・勝利条件：自分自身の生存 + 妖狐陣営の全滅
*/
class Role_indigo_ogre extends Role{
  var $resist_rate = 30;

  function Role_indigo_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS;

    if($this->IsDead() || strpos($victory, 'fox') !== false) return false;
    foreach($USERS->rows as $user){
      if($user->IsLive() && $user->GetCamp(true) == 'fox') return false;
    }
    return true;
  }
}
