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

  function WolfEatCounter(User $target) {
    $actor = $this->GetActor();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->mind_role, $actor->user_no)) {
	$result = DB::$USER->GetHandleName($target->uname, true);
	$target = DB::$USER->GetHandleName($actor->uname, true);
	DB::$ROOM->ResultAbility('PRESAGE_RESULT', $result, $target, $user->user_no);
	break;
      }
    }
  }
}
