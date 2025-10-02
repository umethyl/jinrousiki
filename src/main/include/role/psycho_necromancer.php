<?php
/*
  ◆精神感応者 (psycho_necromancer)
  ○仕様
  ・霊能：前世 (順番依存有り)
*/
RoleManager::LoadFile('necromancer');
class Role_psycho_necromancer extends Role_necromancer{
  function __construct(){ parent::__construct(); }

  function Necromancer($user, $flag){
    global $USERS;

    $str = $USERS->GetHandleName($user->uname, true) . "\t";
    if($flag) return $str . 'stolen';
    $str .= 'psycho_necromancer_';
    if($user->IsRole('changed_therian')) return $str . 'mad';
    if($user->IsRoleGroup('copied')) return $str . 'mania';
    if($user->IsRoleGroup('mad')) return $str . 'wolf';
    if($user->IsLiar()) return $str . 'mad';
    return $str . 'human';
  }
}
