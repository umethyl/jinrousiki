<?php
/*
  ◆狢 (enchant_mad)
  ○仕様
  ・悪戯：迷彩 (同一アイコン)
*/
class Role_enchant_mad extends Role {
  public $mix_in = 'light_fairy';
  public $bad_status = 'same_face';

  function OutputAction() { $this->filter->OutputAction(); }

  function IsVote() { return $this->filter->IsVote(); }

  function IsFinishVote(array $list) { return $this->filter->IsFinishVote($list); }

  function SetVoteNight() { $this->filter->SetVoteNight(); }

  function BadStatus(UserDataSet $USERS) {
    if (! isset(DB::$ROOM->event->{$this->bad_status})) return;
    $target = $USERS->ById(DB::$ROOM->event->{$this->bad_status});
    if (! isset($target->icon_filename)) return;
    foreach ($USERS->rows as $user) $user->icon_filename = $target->icon_filename;
  }
}
