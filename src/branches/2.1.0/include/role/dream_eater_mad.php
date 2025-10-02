<?php
/*
  ◆獏 (dream_eater_mad)
  ○仕様
*/
class Role_dream_eater_mad extends Role {
  public $action = 'DREAM_EAT';
  public $ignore_message = '初日は襲撃できません';

  function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', 'dream_eat', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  //夢食い処理
  function DreamEat(User $user) {
    $actor = $this->GetActor();
    if ($user->IsLiveRole('dummy_guard', true)) { //対象が夢守人なら返り討ちに合う
      DB::$USER->Kill($actor->user_no, 'HUNTED');
      if (! DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->ResultAbility('GUARD_HUNTED', 'hunted', $actor->handle_name, $user->user_no);
      }
      return;
    }

    foreach (RoleManager::LoadFilter('guard_dream') as $filter) { //夢護衛判定
      if ($filter->GuardDream($actor, $user->uname)) return;
    }

    //夢食い判定 (夢系能力者・妖精系)
    if ($user->IsRoleGroup('dummy', 'fairy')) DB::$USER->Kill($user->user_no, 'DREAM_KILLED');
  }
}
