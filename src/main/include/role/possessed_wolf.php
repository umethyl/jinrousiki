<?php
/*
  ◆憑狼 (possessed_wolf)
  ○仕様
  ・襲撃：憑依
*/
RoleManager::LoadFile('wolf');
class Role_possessed_wolf extends Role_wolf {
  protected function OutputResult() {
    if (DB::$ROOM->date > 1) RoleHTML::OutputPossessed();
  }

  function IsMindReadPossessed(User $user) { return $this->GetTalkFlag('wolf'); }

  function WolfKill(User $user) {
    if ($user->IsDummyBoy() || $user->IsCamp('fox') || $user->IsPossessedLimited()) { //スキップ判定
      parent::WolfKill($user);
      return;
    }
    $this->AddStack($user->id, 'possessed', $this->GetWolfVoter()->id);
    $user->dead_flag = true;
    //憑依リセット判定
    if ($user->IsRole('anti_voodoo')) $this->GetWolfVoter()->possessed_reset = true;
  }

  //憑依処理
  function Possessed() {
    $possessed_date = DB::$ROOM->date + 1; //憑依する日を取得
    $followed_list  = null; //恋人後追いリスト
    foreach ($this->GetStack('possessed') as $id => $target_id) {
      $user    = DB::$USER->ByID($id); //憑依者
      $target  = DB::$USER->ByID($target_id); //憑依予定先
      $virtual = $user->GetVirtual(); //現在の憑依先
      if (! isset($user->possessed_reset))  $user->possessed_reset  = null;
      if (! isset($user->possessed_cancel)) $user->possessed_cancel = null;

      // 憑依成立している恋人なら、後追いが発生していないか確認する
      if (! $user->possessed_reset && ! $user->possessed_cancel && $user->IsLovers()) {
	if (is_null($followed_list)) {
	  $followed_list = RoleManager::GetClass('lovers')->Followed(false, true);
	}

	if (in_array($user->id, $followed_list)) $user->possessed_cancel = true;
      }

      if ($user->IsDead(true)) { //憑依者死亡
	if (isset($target->id)) {
	  $target->dead_flag = false; //死亡フラグをリセット
	  DB::$USER->Kill($target->id, 'WOLF_KILLED');
	  if ($target->revive_flag) $target->Update('live', 'live'); //蘇生対応
	}
      }
      elseif ($user->possessed_reset) { //憑依リセット
	if (isset($target->id)) {
	  $target->dead_flag = false; //死亡フラグをリセット
	  DB::$USER->Kill($target->id, 'WOLF_KILLED');
	  if ($target->revive_flag) $target->Update('live', 'live'); //蘇生対応
	}

	if (! $user->IsSame($virtual)) { //憑依中なら元の体に戻される
	  //憑依先のリセット処理
	  $virtual->ReturnPossessed('possessed');
	  $virtual->SaveLastWords();
	  DB::$ROOM->ResultDead($virtual->handle_name, 'POSSESSED_RESET');

	  //見かけ上の蘇生処理
	  $user->ReturnPossessed('possessed_target');
	  $user->SaveLastWords($virtual->handle_name);
	  DB::$ROOM->ResultDead($user->handle_name, 'REVIVE_SUCCESS');
	}
	continue;
      }
      elseif ($user->possessed_cancel || $target->revive_flag) { //憑依失敗
	$target->dead_flag = false; //死亡フラグをリセット
	DB::$USER->Kill($target->id, 'WOLF_KILLED');
	if ($target->revive_flag) $target->Update('live', 'live'); //蘇生対応
	continue;
      }
      else { //憑依成功
	if ($user->IsRole('possessed_wolf')) {
	  $target->dead_flag = false; //死亡フラグをリセット
	  DB::$USER->Kill($target->id, 'POSSESSED_TARGETED'); //憑依先の死亡処理
	  //憑依先が誰かに憑依しているケースがあるので仮想ユーザで上書きする
	  //Ver. 1.5.0 β13 の仕様変更でこのケースはなくなったはず
	  $target = $target->GetVirtual();
	}
	else {
	  DB::$ROOM->ResultDead($target->handle_name, 'REVIVE_SUCCESS');
	  $user->LostAbility();
	}
	$target->AddRole(sprintf('possessed[%d-%d]', $possessed_date, $user->id));

	//憑依処理
	$user->AddRole(sprintf('possessed_target[%d-%d]', $possessed_date, $target->id));
	DB::$ROOM->ResultDead($virtual->handle_name, 'POSSESSED');
	$user->SaveLastWords($virtual->handle_name);
	$user->Update('last_words', null);
      }

      if (! $user->IsSame($virtual)) { //多段憑依対応
	$virtual->ReturnPossessed('possessed');
	if ($user->IsLive(true)) $virtual->SaveLastWords();
      }
    }
  }
}
