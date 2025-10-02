<?php
/*
  ◆ジョーカー (joker)
  ○仕様
  ・勝利判定：非最終所持 or 単独生存
*/
class Role_joker extends Role {
  protected function IgnoreAbility() { return ! $this->GetActor()->IsJoker(); }

  function FilterWin(&$flag) {
    $flag = ! $this->GetActor()->IsJoker() ||
      ($this->IsLive() && count(DB::$USER->GetLivingUsers()) == 1);
  }

  //ジョーカー移動
  function SetJoker() {
    $user    = $this->GetJoker(); //現在の所持者を取得
    $virtual = DB::$USER->ByVirtual($user->user_no)->uname; //仮想ユーザ名を取得
    $uname   = $this->GetVoteTargetUname($virtual); //ジョーカーの投票先
    $this->SetStack($uname, 'joker_uname');
    //Text::p($uname, 'Vote');

    $target = array(); //移動可能者リスト
    $stack  = $this->GetVotedUname($virtual); //ジョーカー投票者
    foreach ($stack as $voter_uname) { //死者と前日所持者を除外
      $voter = DB::$USER->ByRealUname($voter_uname);
      if ($voter->IsLive(true) && ! $voter->IsJoker(true)) $target[] = $voter_uname;
    }
    $this->SetStack($target, 'joker_target');
    //Text::p($stack, 'Target [Voted]');
    //Text::p($target, 'Target [joker]');

    //対象者か現在のジョーカー所持者が処刑者なら無効
    if ($this->IsVoted($uname) || $this->IsVoted($user->uname)) return true;

    if (in_array($uname, $stack)) { //相互投票なら無効 (複数から投票されていた場合は残りからランダム)
      unset($target[array_search($uname, $target)]);
      $this->SetStack($target, 'joker_target');
      //Text::p($target, 'ReduceTarget');
      if (count($target) == 0) return true;
      $uname = Lottery::Get($target);
    }
    elseif (DB::$USER->ByRealUname($uname)->IsDead(true)) { //対象者が死亡していた場合
      if (count($target) == 0) return true;
      $uname = Lottery::Get($target); //ジョーカー投票者から選出
    }
    DB::$USER->ByRealUname($uname)->AddJoker();
    return false;
  }

  //ジョーカー再設定 (ゲーム終了時)
  /* ゲーム終了時のみ、処刑先への移動許可 (それ以外なら本人継承) */
  function FinishJoker() {
    $uname = $this->GetStack('joker_uname');
    $user  = $this->GetJoker();
    $this->IsVoted($uname) && ! $this->IsVoted($user->uname) ?
      DB::$USER->ByRealUname($uname)->AddJoker() : $user->AddJoker();
  }

  //ジョーカー再設定
  /* 生きていたら本人継承 / 処刑者なら前日所持者以外の投票者ランダム / 死亡なら完全ランダム */
  function ResetJoker() {
    $user = $this->GetJoker();
    if ($user->IsLive(true)) {
      $user->AddJoker();
      return;
    }
    $target = $this->GetStack('joker_target');
    $stack  = $this->IsVoted($user->uname) && count($target) > 0 ?
      $target : DB::$USER->GetLivingUsers(true);
    DB::$USER->ByRealUname(Lottery::Get($stack))->AddJoker();
  }

  //現在の所持ユーザ取得
  private function GetJoker() { return DB::$USER->ByID($this->GetStack('joker_id')); }
}
