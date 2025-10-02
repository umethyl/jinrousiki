<?php
/*
  ◆金鬼
  ○仕様
  ・勝利条件：自分自身の生存 + 自分と同列の左側にいる人の全滅 + 村人陣営の勝利
*/
class Role_west_ogre extends Role{
  var $resist_rate = 40;

  function Role_west_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS, $ROLES;

    if($this->IsDead() || $victory != 'human') return false;
    foreach($USERS->rows as $user){
      if($user->user_no >= $ROLES->actor->user_no) return true;
      if($user->user_no > (ceil($ROLES->actor->user_no / 5) - 1 ) * 5 && $user->IsLive()){
	return false;
      }
    }
    return true;
  }
}
