<?php
/*
  ◆舟幽霊 (follow_mad)
  ○仕様
  ・処刑道連れ：投票先がショック死していたら誰か一人をさらにショック死させる
*/
class Role_follow_mad extends Role {
  public $mix_in = ['chicken'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function VoteKillFollowed() {
    $stack = $this->GetStack();
    if (false === is_array($stack)) {
      return;
    }

    $count = 0; //能力発動カウント
    $follow_stack = []; //有効投票先リスト
    foreach ($stack as $uname => $target_uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $target = DB::$USER->ByRealUname($target_uname);
      if ($this->IsVoteKill($target->uname)) {
	continue;
      }

      if ($target->IsOn(UserMode::SUICIDE)) {
	$count++;
      } else {
	$follow_stack[$uname] = $target->id;
      }
    }
    //Text::p($follow_stack, "◆Count [{$this->role}]: {$count}" );
    if ($count < 1) {
      return false;
    }

    $target_stack = []; //対象者リスト
    foreach (RoleManager::Stack()->Get(VoteDayElement::USER_LIST) as $uname) { //情報収集
      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsLive(true) && false === RoleUser::Avoid($user, true)) {
	$target_stack[] = $user->id;
      }
    }
    //Text::p($target_stack, "◆BaseTarget [{$this->role}]" );

    while ($count > 0 && count($target_stack) > 0) { //道連れ処理
      $count--;
      shuffle($target_stack); //配列をシャッフル
      $id = array_shift($target_stack);
      $this->SuddenDeathKill($id); //死亡処理

      if (false === in_array($id, $follow_stack)) { //連鎖判定
	continue;
      }

      $stack = [];
      foreach ($follow_stack as $uname => $target_id) {
	if ($target_id == $id) {
	  $count++;
	} else {
	  $stack[$uname] = $id;
	}
      }
      $follow_stack = $stack;
    }
  }

  protected function GetSuddenDeathType() {
    return 'FOLLOWED';
  }
}
