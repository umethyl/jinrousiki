<?php
/*
  ◆一寸法師 (wanderer_guard)
  ○仕様
  ・人狼襲撃失敗カウンター：護衛貫通
*/
RoleManager::LoadFile('guard');
class Role_wanderer_guard extends Role_guard {
  protected function OutputGuardAddResult() {
    $this->OutputAbilityResult('GUARD_PENETRATION');
  }

  public function WolfEatFailedCounter() {
    $result    = 'GUARD_PENETRATION';
    $vote_data = RoleManager::Stack()->Get('vote_data');
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDead(true) || ! isset($vote_data[$this->action][$user->id])) continue;
      $target = DB::$USER->ByID($vote_data[$this->action][$user->id]);
      $target->AddRole('penetration');
      if (DB::$ROOM->IsOption('seal_message')) continue;
      DB::$ROOM->ResultAbility($result, 'penetration', $target->GetName(), $user->GetID());
    }
  }
}
