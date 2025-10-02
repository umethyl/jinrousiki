<?php
/*
  ◆デスノート (death_note)
  ○仕様
  ・表示：所持日限定
*/
class Role_death_note extends Role {
  public $action     = VoteAction::DEATH_NOTE;
  public $not_action = VoteAction::NOT_DEATH_NOTE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return $this->IsDoom();
  }

  protected function IgnoreAbility() {
    return false === $this->IsDoom();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::DEATH_NOTE;
    RoleHTML::OutputVoteNight(VoteCSS::DEATH_NOTE, $str, $this->action, $this->not_action);
  }

  //投票判定処理
  public function IsVoteDeathNote() {
    /*
      配役設定上、初日に配布されることはなく、
      バグで配布された場合でも IsVoteDate() の判定で false が返るので
      投票無効と判定されて未所持と同等の扱いとなる
    */
    return $this->IsVote();
  }

  //デスノート処理
  public function DeathNoteKill(array $list) {
    foreach ($list as $id => $target_id) {
      if (DB::$USER->ByID($id)->IsDead(true)) { //直前に死んでいたら無効
	continue;
      }
      DB::$USER->Kill($target_id, DeadReason::ASSASSIN_KILLED);
    }
  }

  //デスノート再配布
  public function ResetDeathNote() {
    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsLive(true)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, "◆Target [{$this->role}]");
    if (count($stack) < 1) {
      return;
    }

    $user = DB::$USER->ByID(Lottery::Get($stack));
    $user->AddDoom(0, $this->role);
    DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    DB::$ROOM->StoreDead($user->handle_name, DeadReason::DEATH_NOTE_MOVED);
    DB::$ROOM->ShiftScene();     //日時を元に戻す
  }
}
