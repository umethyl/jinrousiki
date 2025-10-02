<?php
/*
  ◆件 (presage_scanner)
  ○仕様
  ・追加役職：受託者
  ・人狼襲撃：受託者に襲撃者を通知
*/
RoleLoader::LoadFile('mind_scanner');
class Role_presage_scanner extends Role_mind_scanner {
  protected function GetMindRole() {
    return 'mind_presage';
  }

  public function WolfEatCounter(User $voter) {
    $actor = $this->GetActor();
    $role  = $this->GetMindRole();
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $actor->id)) {
	$result = RoleAbility::PRESAGE;
	DB::$ROOM->ResultAbility($result, $voter->GetName(), $actor->GetName(), $user->id);
	break;
      }
    }
  }
}
