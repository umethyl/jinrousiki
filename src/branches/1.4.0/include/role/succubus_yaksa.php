<?php
/*
  ◆荼枳尼天
  ○仕様
  ・勝利条件：自分自身の生存 + 男性の全滅
*/
class Role_succubus_yaksa extends Role{
  var $resist_rate = 20;

  function Role_succubus_yaksa(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    global $USERS;

    if($this->IsDead()) return false;
    foreach($USERS->rows as $user){
      if(! $this->IsSameUser($user->uname) && $user->IsLive() && $user->sex == 'male'){
	return false;
      }
    }
    return true;
  }
}
