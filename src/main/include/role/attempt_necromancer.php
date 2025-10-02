<?php
/*
  ◆蟲姫 (attempt_necromancer)
  ○仕様
  ・霊能：死を免れた人
*/
RoleManager::LoadFile('necromancer');
class Role_attempt_necromancer extends Role_necromancer {
  function Necromancer(User $user, $data) {
    $stack = array();
    if ($user->IsLive(true)) $stack[$user->user_no] = true; //人狼襲撃
    foreach (array('ASSASSIN_DO', 'OGRE_DO') as $action) { //暗殺・人攫い
      foreach ($data[$action] as $id) {
	if (DB::$USER->ByID($id)->IsLive(true)) $stack[$id] = true;
      }
    }

    $str_stack = array();
    foreach (array_keys($stack) as $id) { //仮想ユーザの ID 順に出力
      $user = DB::$USER->ByVirtual($id);
      $str_stack[$user->user_no] = $user->handle_name;
    }
    ksort($str_stack);
    $action = 'ATTEMPT_NECROMANCER_RESULT';
    foreach ($str_stack as $target) DB::$ROOM->ResultAbility($action, 'attempt', $target);
  }
}
