<?php
/*
  ◆蟲姫 (attempt_necromancer)
  ○仕様
  ・霊能：死を免れた人
*/
RoleManager::LoadFile('necromancer');
class Role_attempt_necromancer extends Role_necromancer{
  function __construct(){ parent::__construct(); }

  function Necromancer($user, $data){
    global $ROOM, $USERS;

    $stack = array();
    if($user->IsLive(true)) $stack[$user->uname] = true; //人狼襲撃
    foreach($data['ASSASSIN_DO'] as $uname){ //暗殺
      if($USERS->ByUname($uname)->IsLive(true)) $stack[$uname] = true;
    }
    foreach($data['OGRE_DO'] as $uname){ //人攫い
      if($USERS->ByUname($uname)->IsLive(true)) $stack[$uname] = true;
    }
    //PrintData($stack);
    $str_stack = array();
    foreach(array_keys($stack) as $uname){ //仮想ユーザの ID 順に出力
      $user = $USERS->ByVirtualUname($uname);
      $str_stack[$user->user_no] = $user->handle_name . "\t" . 'attempt';
    }
    ksort($str_stack);
    foreach($str_stack as $str) $ROOM->SystemMessage($str, 'ATTEMPT_NECROMANCER_RESULT');
  }
}
