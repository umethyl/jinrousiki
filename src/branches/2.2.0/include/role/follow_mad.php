<?php
/*
  ◆舟幽霊 (follow_mad)
  ○仕様
  ・道連れ：投票先がショック死していたら誰か一人をさらにショック死させる
*/
class Role_follow_mad extends Role {
  public $sudden_death = 'FOLLOWED';

  function SetVoteDay($uname) {
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  function Followed($user_list) {
    if (! is_array($stack = $this->GetStack())) return;

    $count = 0; //能力発動カウント
    $follow_stack = array(); //有効投票先リスト
    foreach ($stack as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $target = DB::$USER->ByRealUname($target_uname);
      if ($this->IsVoted($target->uname)) continue;
      $target->suicide_flag ? $count++ : $follow_stack[$uname] = $target->id;
    }
    //Text::p($follow_stack, $this->role . ': ' . $count);
    if ($count < 1) return false;

    $target_stack = array(); //対象者リスト
    foreach ($user_list as $uname) { //情報収集
      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsLive(true) && ! $user->IsAvoid(true)) $target_stack[] = $user->id;
    }
    //Text::p($target_stack, "BaseTarget [{$this->role}]" );

    while ($count > 0 && count($target_stack) > 0) { //道連れ処理
      $count--;
      shuffle($target_stack); //配列をシャッフル
      $id = array_shift($target_stack);
      $this->SuddenDeathKill($id); //死亡処理

      if (! in_array($id, $follow_stack)) continue;//連鎖判定
      $stack = array();
      foreach ($follow_stack as $uname => $id) {
	$id == $id ? $count++ : $stack[$uname] = $id;
      }
      $follow_stack = $stack;
    }
  }
}
