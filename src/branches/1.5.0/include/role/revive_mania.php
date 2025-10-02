<?php
/*
  ◆五徳猫 (revive_mania)
  ○仕様
  ・人狼襲撃：コピー先蘇生
*/
RoleManager::LoadFile('unknown_mania');
class Role_revive_mania extends Role_unknown_mania{
  function __construct(){ parent::__construct(); }

  function WolfEatCounter($user){
    global $ROOM, $USERS;

    if($ROOM->IsOpenCast() || $ROOM->IsEvent('no_revive') ||
       is_null($id = $this->GetActor()->GetMainRoleTarget())){
      return;
    }
    $target = $USERS->ByID($id);
    if($target->IsDead(true) && ! $target->IsReviveLimited()){
      //憑依対応
      $real = $USERS->ByReal($target->user_no);
      if($target !== $real){
	$real->ReturnPossessed('possessed_target');

	$target->ReturnPossessed('possessed');
	$target->Update('live', 'live');
	$target->revive_flag = true;
	$ROOM->SystemMessage($real->handle_name, 'REVIVE_SUCCESS');
      }
      else {
	$target->Revive();
      }
    }
  }
}
