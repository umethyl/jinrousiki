<?php
/*
  ◆デスノート (death_note)
  ○仕様
*/
class Role_death_note extends Role {
  public $action     = 'DEATH_NOTE_DO';
  public $not_action = 'DEATH_NOTE_NOT_DO';

  protected function IgnoreAbility() { return ! $this->IsDoom(); }

  function OutputAction() {
    RoleHTML::OutputVote('death-note-do', 'death_note_do', $this->action, $this->not_action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }
}
