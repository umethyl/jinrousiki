<?php
/*
  ◆鮮血夫人 (follow_vampire)
  ○仕様
  ・処刑：吸血死 (自分の感染者)
  ・人狼襲撃：吸血死 (自分の感染者)
*/
RoleLoader::LoadFile('vampire');
class Role_follow_vampire extends Role_vampire {
  public function VoteKillCounter(array $list) {
    $this->InfectFollowed();
  }

  public function WolfEatCounter(User $user) {
    $this->InfectFollowed();
  }

  //巻き添え吸血死処理
  private function InfectFollowed() {
    $id   = $this->GetID();
    $role = 'infected';
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id) && $user->IsLive(true) && ! RoleUser::IsAvoid($user)) {
	DB::$USER->Kill($user->id, DeadReason::VAMPIRE_KILLED);
      }
    }
  }
}
