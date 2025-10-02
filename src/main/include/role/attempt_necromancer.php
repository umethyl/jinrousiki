<?php
/*
  ◆蟲姫 (attempt_necromancer)
  ○仕様
  ・霊能：死を免れた人
*/
RoleLoader::LoadFile('necromancer');
class Role_attempt_necromancer extends Role_necromancer {
  public $result = RoleAbility::ATTEMPT_NECROMANCER;

  //霊能 (夜発動型)
  public function NecromancerNight() {
    $stack = array();

    //-- 人狼襲撃 --//
    $user = RoleManager::Stack()->Get('wolf_target');
    if ($user->IsLive(true)) {
      $stack[$user->id] = true;
    }

    //-- 暗殺・人攫い --//
    $vote_data = RoleManager::GetVoteData();
    foreach (array(VoteAction::ASSASSIN, VoteAction::OGRE) as $action) {
      foreach ($vote_data[$action] as $id) {
	if (DB::$USER->ByID($id)->IsLive(true)) {
	  $stack[$id] = true;
	}
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
