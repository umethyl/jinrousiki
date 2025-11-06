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
    $stack = [];

    //-- 人狼襲撃 --//
    $this->SaveNecromancerNightUser(RoleManager::Stack()->Get('wolf_target'), $stack);

    //-- 暗殺・人攫い --//
    $vote_data = RoleManager::GetVoteData();
    foreach ([VoteAction::ASSASSIN, VoteAction::OGRE] as $action) {
      $this->SaveNecromancerNight($vote_data[$action], $stack);
    }

    //-- 魔砲使い --//
    foreach ([VoteAction::SPARK_WIZARD] as $action) {
      foreach ($vote_data[$action] as $id => $list) {
	$this->SaveNecromancerNight(Text::Parse($list), $stack);
      }
    }

    $str_stack = [];
    foreach (array_keys($stack) as $id) { //仮想ユーザの ID 順に出力
      $user = DB::$USER->ByVirtual($id);
      $str_stack[$user->id] = $user->handle_name;
    }
    ksort($str_stack);

    foreach ($str_stack as $target) {
      DB::$ROOM->StoreAbility($this->result, 'attempt', $target);
    }
  }

  //霊能発動対象ユーザー登録
  private function SaveNecromancerNightUser(User $user, array &$stack) {
    if ($user->IsLive(true)) {
      $stack[$user->id] = true;
    }
  }

  //霊能発動対象登録
  private function SaveNecromancerNight(array $list, array &$stack) {
    foreach ($list as $id) {
      $this->SaveNecromancerNightUser(DB::$USER->ByID($id), $stack);
    }
  }
}
