<?php
/*
  ◆一寸法師 (wanderer_guard)
  ○仕様
  ・能力結果：護衛貫通追加
  ・人狼襲撃失敗カウンター：護衛貫通
*/
RoleLoader::LoadFile('guard');
class Role_wanderer_guard extends Role_guard {
  protected function OutputGuardAddResult() {
    RoleHTML::OutputResult(RoleAbility::PENETRATION);
  }

  public function WolfEatFailedCounter() {
    $result    = RoleAbility::PENETRATION;
    $vote_data = RoleManager::GetVoteData();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDead(true) ||
	  false === ArrayFilter::IsAssocKey($vote_data, $this->action, $user->id)) {
	continue;
      }

      $target = DB::$USER->ByID($vote_data[$this->action][$user->id]);
      $target->AddRole('penetration');
      if (false === DB::$ROOM->IsOption('seal_message')) {
	DB::$ROOM->StoreAbility($result, 'penetration', $target->GetName(), $user->GetID());
      }
    }
  }
}
