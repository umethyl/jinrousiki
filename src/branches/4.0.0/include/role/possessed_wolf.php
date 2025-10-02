<?php
/*
  ◆憑狼 (possessed_wolf)
  ○仕様
  ・能力結果：憑依先
  ・襲撃：憑依
*/
RoleLoader::LoadFile('wolf');
class Role_possessed_wolf extends Role_wolf {
  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  protected function OutputAddResult() {
    RoleHTML::OutputPossessed();
  }

  public function IsMindReadPossessed(User $user) {
    return $this->GetTalkFlag('wolf');
  }

  protected function IgnoreWolfKill(User $user) {
    //スキップ判定 (身代わり君 > 生存状態 > 無効陣営 > 憑依制限)
    if ($user->IsDummyBoy()) {
      return false;
    } elseif ($user->IsDead(true)) {
      return false;
    } elseif ($user->IsCamp(Camp::FOX)) {
      return false;
    } elseif (RoleUser::LimitedPossessed($user)) {
      return false;
    }

    //憑依予約処理
    $this->AddStack($user->id, RoleVoteSuccess::POSSESSED, $this->GetWolfVoter()->id);
    $user->Flag()->On(UserMode::DEAD);

    if ($user->IsRole('anti_voodoo')) { //憑依リセット判定
      $this->GetWolfVoter()->Flag()->On(UserMode::POSSESSED_RESET);
    }
    return true;
  }

  //憑依処理
  final public function Possessed() {
    $possessed_date = DB::$ROOM->date + 1; //憑依する日を取得
    $followed_list  = null; //恋人後追いリスト
    foreach ($this->GetStack(RoleVoteSuccess::POSSESSED) as $id => $target_id) {
      $user    = DB::$USER->ByID($id);        //憑依者
      $target  = DB::$USER->ByID($target_id); //憑依予定先
      $virtual = $user->GetVirtual();         //現在の憑依先

      //憑依成立している恋人なら、後追いが発生していないか確認する
      if ($user->IsOff(UserMode::POSSESSED_RESET) && $user->IsOff(UserMode::POSSESSED_CANCEL) &&
	  $user->IsRole('lovers')) {
	if (is_null($followed_list)) {
	  $followed_list = RoleLoader::Load('lovers')->Followed(false, true);
	}

	if (in_array($user->id, $followed_list)) {
	  $user->Flag()->On(UserMode::POSSESSED_CANCEL);
	}
      }

      //憑依者死亡 > 憑依リセット > 憑依失敗 > 憑依成功
      if ($user->IsDead(true)) {
	if (isset($target->id)) $this->PossessedCancelWolfKill($target);
      } elseif ($user->IsOn(UserMode::POSSESSED_RESET)) {
	if (isset($target->id)) $this->PossessedCancelWolfKill($target);
	if (! $user->IsSame($virtual)) { //憑依中なら元の体に戻される
	  //憑依先のリセット処理
	  $virtual->ReturnPossessed('possessed');
	  $virtual->StoreLastWords();
	  DB::$ROOM->ResultDead($virtual->handle_name, DeadReason::POSSESSED_RESET);

	  //見かけ上の蘇生処理
	  $user->ReturnPossessed('possessed_target');
	  $user->StoreLastWords($virtual->handle_name);
	  DB::$ROOM->ResultDead($user->handle_name, DeadReason::REVIVE_SUCCESS);
	}
	continue;
      } elseif ($user->IsOn(UserMode::POSSESSED_CANCEL) || $target->IsOn(UserMode::REVIVE)) {
	$this->PossessedCancelWolfKill($target);
	continue;
      } else {
	if ($user->IsRole('possessed_wolf')) {
	  $target->Flag()->Off(UserMode::DEAD); //死亡フラグをリセット
	  DB::$USER->Kill($target->id, DeadReason::POSSESSED_TARGETED); //憑依先の死亡処理
	  //憑依先が誰かに憑依しているケースがあるので仮想ユーザで上書きする
	  //Ver. 1.5.0 β13 の仕様変更でこのケースはなくなったはず
	  $target = $target->GetVirtual();
	} else {
	  DB::$ROOM->ResultDead($target->handle_name, DeadReason::REVIVE_SUCCESS);
	  $user->LostAbility();
	}
	$target->AddRole(sprintf('possessed[%d-%d]', $possessed_date, $user->id));

	//憑依処理
	$user->AddRole(sprintf('possessed_target[%d-%d]', $possessed_date, $target->id));
	DB::$ROOM->ResultDead($virtual->handle_name, DeadReason::POSSESSED);
	$user->StoreLastWords($virtual->handle_name);
	$user->Update('last_words', null);
      }

      if (! $user->IsSame($virtual)) { //多段憑依対応
	$virtual->ReturnPossessed('possessed');
	if ($user->IsLive(true)) {
	  $virtual->StoreLastWords();
	}
      }
    }
  }

  //憑依失敗時の通常人狼襲撃死処理
  private function PossessedCancelWolfKill(User $user) {
    $user->Flag()->Off(UserMode::DEAD); //死亡フラグをリセット
    DB::$USER->Kill($user->id, DeadReason::WOLF_KILLED);
    if ($user->IsOn(UserMode::REVIVE)) { //蘇生対応
      $user->UpdateLive(UserLive::LIVE);
    }
  }
}
