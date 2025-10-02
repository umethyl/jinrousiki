<?php
/*
  ◆精神感応者 (psycho_necromancer)
  ○仕様
  ・霊能：前世 (順番依存有り)
*/
RoleManager::LoadFile('necromancer');
class Role_psycho_necromancer extends Role_necromancer {
  function Necromancer(User $user, $flag) {
    if ($flag) return 'stolen';
    $str = 'psycho_necromancer_';
    if ($user->IsRoleGroup('copied'))        return $str . 'mania';
    if ($user->IsRole('changed_therian'))    return $str . 'mad';
    if ($user->IsRole('changed_vindictive')) return $str . 'child_fox';
    if ($user->IsMainGroup('mad'))           return $str . 'wolf';
    if ($user->IsLiar())                     return $str . 'mad';
    return $str . 'human';
  }
}
