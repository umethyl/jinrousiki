<?php
/*
  ◆件 (presage_scanner)
  ○仕様
  ・追加役職：受託者
  ・人狼襲撃：受託者に襲撃者を通知
*/
RoleManager::LoadFile('mind_scanner');
class Role_presage_scanner extends Role_mind_scanner {
  public $mind_role = 'mind_presage';

  function WolfEatCounter(User $voter) {
    $actor  = $this->GetActor();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->mind_role, $actor->id)) {
	DB::$ROOM->ResultAbility('PRESAGE_RESULT', $voter->GetName(), $actor->GetName(), $user->id);
	break;
      }
    }
  }
}
