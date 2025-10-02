<?php
/*
  ◆ジョーカー (joker)
  ○仕様
  ・表示：所持日限定
  ・勝利判定：非最終所持 or 単独生存
*/
class Role_joker extends Role {
  protected function IgnoreAbility() {
    return ! $this->GetActor()->IsJoker();
  }

  public function FilterWin(&$flag) {
    $flag = ! $this->GetActor()->IsJoker() ||
      ($this->IsLive() && count(DB::$USER->GetLivingUsers()) == 1);
  }

  //ジョーカー所持者判定
  public function IsJoker(User $user, $shift = false) {
    if (DB::$ROOM->IsFinished()) {
      if (! isset($user->joker_flag)) $this->SetAllJoker();
      return $user->joker_flag;
    }
    elseif ($user->IsDead()) {
      return false;
    }

    $date = DB::$ROOM->date - ($shift ? 1 : 0);
    if ($date == 1 || DB::$ROOM->IsNight()) $date++;
    return $user->GetDoomDate($this->role) == $date;
  }

  //ジョーカー所持者初期化
  public function InitializeJoker() {
    RoleManager::Stack()->Set('joker_id', $this->SetAllJoker());
  }

  //ジョーカーの最終所持者判定
  public function SetAllJoker() {
    $id = null;
    $max_date = 1;
    foreach (DB::$USER->rows as $user) {
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

  //ジョーカー移動
  public function SetJoker() {
    $user    = $this->GetJoker(); //現在の所持者を取得
    $virtual = $user->GetVirtual()->uname; //仮想ユーザ名を取得
    $uname   = $this->GetVoteTargetUname($virtual); //ジョーカーの投票先
    $this->SetStack($uname, 'joker_uname');
    //Text::p($uname, '◆Vote');

    $target = array(); //移動可能者リスト
    $stack  = $this->GetVotedUname($virtual); //ジョーカー投票者
    foreach ($stack as $voter_uname) { //死者と前日所持者を除外
      $voter = DB::$USER->ByRealUname($voter_uname);
      if ($voter->IsLive(true) && ! $voter->IsJoker(true)) $target[] = $voter_uname;
    }
    $this->SetStack($target, 'joker_target');
    //Text::p($stack,  '◆Target [Voted]');
    //Text::p($target, '◆Target [joker]');

    //対象者か現在のジョーカー所持者が処刑者なら無効
    if ($this->IsVoted($uname) || $this->IsVoted($user->uname)) return true;

    //相互投票なら無効 (複数から投票されていた場合は残りからランダム)
    if (in_array($uname, $stack)) {
      unset($target[array_search($uname, $target)]);
      $this->SetStack($target, 'joker_target');
      //Text::p($target, '◆ReduceTarget');
      if (count($target) == 0) return true;
      $uname = Lottery::Get($target);
    }
    elseif (DB::$USER->ByRealUname($uname)->IsDead(true)) { //対象者が死亡していた場合
      if (count($target) == 0) return true;
      $uname = Lottery::Get($target); //ジョーカー投票者から選出
    }
    $this->AddJoker(DB::$USER->ByRealUname($uname));
    return false;
  }

  //ジョーカー再配布 (シーン切り替え時)
  public function ResetJoker($shift = false) {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsDead(true)) continue;
      if ($user->IsJoker()) return; //現在の所持者が生存していた場合はスキップ
      $stack[] = $user;
    }
    if (count($stack) > 0) $this->AddJoker(Lottery::Get($stack), $shift);
  }

  //ジョーカー再設定 (処刑処理用)
  /* 生きていたら本人継承 / 処刑者なら前日所持者以外の投票者ランダム / 死亡なら完全ランダム */
  public function ResetVoteJoker() {
    $user = $this->GetJoker();
    if ($user->IsLive(true)) {
      $this->AddJoker($user);
      return;
    }

    $target = $this->GetStack('joker_target');
    if ($this->IsVoted($user->uname) && count($target) > 0) {
      $stack = $target;
    } else {
      $stack = DB::$USER->GetLivingUsers(true);
    }
    $this->AddJoker(DB::$USER->ByRealUname(Lottery::Get($stack)));
  }

  //ジョーカー再設定 (ゲーム終了時)
  /* ゲーム終了時のみ、処刑先への移動許可 (それ以外なら本人継承) */
  public function FinishJoker() {
    $uname = $this->GetStack('joker_uname');
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

  //現在の所持ユーザ取得
  private function GetJoker() {
    return DB::$USER->ByID($this->GetStack('joker_id'));
  }

  //ジョーカーの移動処理
  private function AddJoker(User $user, $shift = false) {
    if ($shift) DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    $user->AddDoom(1, 'joker');
    DB::$ROOM->ResultDead($user->handle_name, 'JOKER_MOVED');
    if ($shift) DB::$ROOM->ShiftScene(); //日時を元に戻す
  }
}
