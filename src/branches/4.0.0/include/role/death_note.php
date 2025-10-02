<?php
/*
  ◆デスノート (death_note)
  ○仕様
  ・表示：所持日限定
*/
class Role_death_note extends Role {
  public $action      = VoteAction::DEATH_NOTE;
  public $not_action  = VoteAction::NOT_DEATH_NOTE;
  public $action_date = RoleActionDate::AFTER;

  protected function IsAddVote() {
    return $this->IsDoom();
  }

  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::DEATH_NOTE;
    RoleHTML::OutputVote(VoteCSS::DEATH_NOTE, $str, $this->action, $this->not_action);
  }

  //投票判定処理
  public function IsVoteDeathNote() {
    /*
      配役設定上、初日に配布されることはなく、バグで配布された場合でも
      集計処理は実施されないので、ここではそのまま投票させておく。
      逆にスキップ判定を実施した場合、初日投票能力者が詰む。
    */
    $this->action_date = null;
    return $this->IsVote();
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
    if (count($stack) < 1) return;

    $user = DB::$USER->ByID(Lottery::Get($stack));
    $user->AddDoom(0, $this->role);
    DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    DB::$ROOM->ResultDead($user->handle_name, DeadReason::DEATH_NOTE_MOVED);
    DB::$ROOM->ShiftScene();     //日時を元に戻す
  }
}
