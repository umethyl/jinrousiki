<?php
/*
  ◆ジョーカー (joker)
  ○仕様
  ・表示：所持日限定
  ・勝利判定：非最終所持 or 単独生存
*/
class Role_joker extends Role {
  protected function IgnoreAbility() {
    return ! $this->IsJoker($this->GetActor());
  }

  public function FilterWin(&$flag) {
    $flag = $this->IgnoreAbility() || ($this->IsActorLive() && DB::$USER->CountLive() == 1);
  }

  //ジョーカー所持者初期化
  public function InitializeJoker() {
    $this->SetStack(['id' => $this->SetAllJoker()]);
  }

  //ジョーカー所持者判定
  public function IsJoker(User $user, $shift = false) {
    if (DB::$ROOM->IsFinished()) {
      if (! isset($user->joker_flag)) {
	$this->SetAllJoker();
      }
      return $user->joker_flag;
    } elseif ($user->IsDead()) {
      return false;
    }

    $date = DB::$ROOM->date - ($shift ? 1 : 0);
    if ($date == 1 || DB::$ROOM->IsNight()) $date++;
    return $user->GetDoomDate($this->role) == $date;
  }

  //ジョーカー移動
  public function SetJoker() {
    $user    = $this->GetJoker(); //現在の所持者
    $virtual = $user->GetVirtual()->uname; //仮想ユーザ名
    $uname   = $this->GetVoteTargetUname($virtual); //ジョーカーの投票先

    //Stack 格納
    $stack = $this->GetStack();
    $stack['uname'] = $uname;
    //Text::p($uname, '◆Vote');

    $target_list = []; //移動可能者リスト
    $voter_list  = $this->GetVotedUname($virtual); //ジョーカー投票者
    foreach ($voter_list as $voter_uname) { //死者と前日所持者を除外
      $voter = DB::$USER->ByRealUname($voter_uname);
      if ($voter->IsLive(true) && ! $this->IsKeepJoker($voter, true)) {
	$target_list[] = $voter_uname;
      }
    }
    $stack['target_list'] = $target_list;
    $this->SetStack($stack); //まとめて Stack 登録
    //Text::p($voter_list,  '◆Target [Voter]');
    //Text::p($target_list, '◆Target [joker]');

    //対象者か現在のジョーカー所持者が処刑者なら無効
    if ($this->IsVoted($uname) || $this->IsVoted($user->uname)) {
      return true;
    }

    //相互投票なら無効 (複数から投票されていた場合は残りからランダム)
    if (in_array($uname, $voter_list)) {
      //対象者から除外して再登録
      ArrayFilter::Delete($target_list, $uname);
      $stack['target_list'] = $target_list;
      $this->SetStack($stack);
      //Text::p($target_list, '◆ReduceTarget');
      if (count($target_list) == 0) {
	return true;
      }
      $uname = Lottery::Get($target_list);
    } elseif (DB::$USER->ByRealUname($uname)->IsDead(true)) { //対象者が死亡していた場合
      if (count($target_list) == 0) {
	return true;
      }
      $uname = Lottery::Get($target_list); //ジョーカー投票者から選出
    }
    $this->AddJoker(DB::$USER->ByRealUname($uname));
    return false;
  }

  //ジョーカー再配布 (シーン切り替え時)
  //所持者が生存していたら維持 > 生存者ランダム
  public function ResetJoker($shift = false) {
    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead(true)) continue;
      if ($this->IsKeepJoker($user)) {
	return;
      }
      $stack[] = $user;
    }
    if (count($stack) > 0) {
      $this->AddJoker(Lottery::Get($stack), $shift);
    }
  }

  //ジョーカー再設定 (処刑処理用)
  //生きていたら本人継承 > 処刑者なら前日所持者以外の投票者ランダム > 生存者ランダム
  public function ResetVoteJoker() {
    $user = $this->GetJoker();
    if ($user->IsLive(true)) {
      $this->AddJoker($user);
      return;
    }

    $target_list = $this->GetStack()['target_list'];
    if ($this->IsVoted($user->uname) && count($target_list) > 0) {
      $stack = $target_list;
    } else {
      $stack = DB::$USER->SearchLive(true);
    }
    $this->AddJoker(DB::$USER->ByRealUname(Lottery::Get($stack)));
  }

  //ジョーカー再設定 (ゲーム終了時)
  //ゲーム終了時のみ、処刑先への移動許可 (それ以外なら本人継承)
  public function FinishJoker() {
    $uname = $this->GetStack()['uname'];
    $user  = $this->GetJoker();
    if ($this->IsVoted($uname) && ! $this->IsVoted($user->uname)) {
      $this->AddJoker(DB::$USER->ByRealUname($uname));
    } else {
      $this->AddJoker($user);
    }
  }

  //ジョーカー再設定 (引き分け終了時)
  public function FinishDrawJoker() {
    $this->AddJoker($this->GetJoker());
  }

  //ジョーカーの最終所持者判定
  private function SetAllJoker() {
    $id = null;
    $max_date = 1;
    foreach (DB::$USER->Get() as $user) {
      $user->joker_flag = false;
      if (! $user->IsRole($this->role)) continue;
      $date = $user->GetDoomDate($this->role);
      if ($date > $max_date || ($date == $max_date && $user->IsLive())) {
	$id = $user->id;
	$max_date = $date;
      }
    }
    DB::$USER->ByID($id)->joker_flag = true;
    return $id;
  }

  //現在の所持ユーザ取得
  private function GetJoker() {
    return DB::$USER->ByID($this->GetStack()['id']);
  }

  //現ジョーカー所持者判定
  private function IsKeepJoker(User $user, $shift = false) {
    return $user->IsRole($this->role) && $this->IsJoker($user, $shift);
  }

  //ジョーカーの移動処理
  private function AddJoker(User $user, $shift = false) {
    if ($shift) DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    $user->AddDoom(1, 'joker');
    DB::$ROOM->ResultDead($user->handle_name, DeadReason::JOKER_MOVED);
    if ($shift) DB::$ROOM->ShiftScene();     //日時を元に戻す
  }
}
