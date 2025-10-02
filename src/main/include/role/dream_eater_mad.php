<?php
/*
  ◆獏 (dream_eater_mad)
  ○仕様
*/
class Role_dream_eater_mad extends Role {
  public $action = VoteAction::DREAM;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::WOLF, RoleAbilityMessage::DREAM_EATER, $this->action);
  }

  //夢食い処理
  public function DreamEat(User $user) {
    $actor = $this->GetActor();
    if ($user->IsLiveRole('dummy_guard', true)) { //対象が夢守人なら返り討ちに合う
      DB::$USER->Kill($actor->id, DeadReason::HUNTED);
      if (false === DB::$ROOM->IsOption('seal_message')) { //狩りメッセージを登録
	DB::$ROOM->StoreAbility(RoleAbility::HUNTED, 'hunted', $actor->handle_name, $user->id);
      }
      return;
    }

    foreach (RoleLoader::LoadFilter('guard_dream') as $filter) { //対夢食い護衛判定
      if ($filter->GuardDreamEat($actor, $user->id)) {
	return;
      }
    }

    //夢食い判定 (夢系能力者・妖精系)
    if (RoleUser::IsDream($user) && false === RoleUser::IsAvoidLovers($user, true)) {
      DB::$USER->Kill($user->id, DeadReason::DREAM_KILLED);
    }
  }
}
