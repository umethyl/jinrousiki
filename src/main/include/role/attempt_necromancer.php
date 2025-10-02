<?php
/*
  ◆蟲姫 (attempt_necromancer)
  ○仕様
  ・霊能：死を免れた人
*/
RoleManager::LoadFile('necromancer');
class Role_attempt_necromancer extends Role_necromancer {
  public $result = 'ATTEMPT_NECROMANCER_RESULT';

  //霊能 (夜発動型)
  public function NecromancerNight() {
    $stack = array();

    $user = RoleManager::GetStack('wolf_target');
    if ($user->IsLive(true)) $stack[$user->id] = true; //人狼襲撃

    $data = RoleManager::GetStack('vote_data');
    foreach (array('ASSASSIN_DO', 'OGRE_DO') as $action) { //暗殺・人攫い
      foreach ($data[$action] as $id) {
	if (DB::$USER->ByID($id)->IsLive(true)) $stack[$id] = true;
      }
    }

    $str_stack = array();
    foreach (array_keys($stack) as $id) { //仮想ユーザの ID 順に出力
      $user = DB::$USER->ByVirtual($id);
      $str_stack[$user->id] = $user->handle_name;
    }
    ksort($str_stack);

    foreach ($str_stack as $target) {
      DB::$ROOM->ResultAbility($this->result, 'attempt', $target);
    }
  }
}
