<?php
/*
  ◆獏 (dream_eater_mad)
  ○仕様
*/
class Role_dream_eater_mad extends Role {
  public $action = 'DREAM_EAT';

  function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', 'dream_eat', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日は襲撃できません'; }

  //夢食い処理
  final function DreamEat(User $user) {
    $actor = $this->GetActor();
    if ($user->IsLiveRole('dummy_guard', true)) { //対象が夢守人なら返り討ちに合う
      DB::$USER->Kill($actor->id, 'HUNTED');
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $actor->handle_name, $user->id);
      }
      return;
    }

    foreach (RoleManager::LoadFilter('guard_dream') as $filter) { //対夢食い護衛判定
      if ($filter->GuardDream($actor, $user->id)) return;
    }

    //夢食い判定 (夢系能力者・妖精系)
    if ($user->IsRoleGroup('dummy') || $user->IsMainGroup('fairy')) {
      DB::$USER->Kill($user->id, 'DREAM_KILLED');
    }
  }
}
