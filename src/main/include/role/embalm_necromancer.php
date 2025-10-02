<?php
/*
  ◆死化粧師 (embalm_necromancer)
  ○仕様
  ・霊能：処刑投票先との陣営比較
*/
RoleManager::LoadFile('necromancer');
class Role_embalm_necromancer extends Role_necromancer{
  function __construct(){ parent::__construct(); }

  function Necromancer($user, $flag){
    global $USERS;

    $str = $USERS->GetHandleName($user->uname, true) . "\t";
    if($flag) return $str . 'stolen';
    $target = $this->GetVoteUser($user->uname)->GetCamp(true);
    return $str . 'embalm_' . ($user->GetCamp(true) == $target ? 'agony' : 'reposeful');
  }
}
