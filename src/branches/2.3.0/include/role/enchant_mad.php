<?php
/*
  ◆狢 (enchant_mad)
  ○仕様
  ・悪戯：迷彩 (同一アイコン)
*/
class Role_enchant_mad extends Role {
  public $mix_in = 'light_fairy';
  public $bad_status = 'same_face';

  public function OutputAction() {
    $this->filter->OutputAction();
  }

  public function IsVote() {
    return $this->filter->IsVote();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
  }

  public function IsFinishVote(array $list) {
    return $this->filter->IsFinishVote($list);
  }

  public function BadStatus(UserData $USERS) {
    if (! isset(DB::$ROOM->event->{$this->bad_status})) return;

    $target = $USERS->ByID(DB::$ROOM->event->{$this->bad_status});
    if (! isset($target->icon_filename)) return;
    foreach ($USERS->rows as $user) {
      $user->icon_filename = $target->icon_filename;
    }
  }
}
