<?php
/*
  ◆デスノート (death_note)
  ○仕様
  ・表示：所持日限定
*/
class Role_death_note extends Role {
  public $action     = 'DEATH_NOTE_DO';
  public $not_action = 'DEATH_NOTE_NOT_DO';
  public $action_date_type = 'after';

  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  public function OutputAction() {
    RoleHTML::OutputVote('death-note-do', 'death_note_do', $this->action, $this->not_action);
  }

  //デスノート再配布
  public function ResetDeathNote() {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive(true)) $stack[] = $user->id;
    }
    //Text::p($stack, "◆Target [{$this->role}]");
    if (count($stack) < 1) return;

    $user = DB::$USER->ByID(Lottery::Get($stack));
    $user->AddDoom(0, 'death_note');
    DB::$ROOM->ShiftScene(true); //一時的に前日に巻戻す
    DB::$ROOM->ResultDead($user->handle_name, 'DEATH_NOTE_MOVED');
    DB::$ROOM->ShiftScene();
  }
}
