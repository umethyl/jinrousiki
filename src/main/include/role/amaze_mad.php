<?php
/*
  ◆傘化け (amaze_mad)
  ○仕様
  ・処刑投票：悪戯付加 (投票結果隠蔽用)
  ・悪戯：投票結果隠蔽
*/
RoleManager::LoadFile('critical_mad');
class Role_amaze_mad extends Role_critical_mad{
  public $bad_status = 'blind_vote';
  function __construct(){ parent::__construct(); }

  function VoteAction(){
    global $ROOM, $USERS;

    $flag   = false;
    $target = $USERS->ByRealUname($this->GetVoteKill());
    foreach($this->GetStack() as $uname => $target_uname){
      if(! $this->IsVoted($target_uname)) continue;
      $flag = true;
      $id   = $USERS->ByUname($uname)->user_no;
      $target->AddRole("bad_status[{$id}-{$ROOM->date}]");
    }
    if($flag) $ROOM->SystemMessage($USERS->GetHandleName($target->uname, true), 'BLIND_VOTE');
  }

  function SetBadStatus($user){
    global $ROOM;
    $ROOM->event->{$this->bad_status} = true;
  }
}
