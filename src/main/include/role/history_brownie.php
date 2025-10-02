<?php
/*
  ◆白澤 (history_brownie)
  ○仕様
  ・特殊イベント (夜)：夜スキップ
*/
class Role_history_brownie extends Role{
  public $event_night = 'skip_night';
  function __construct(){ parent::__construct(); }

  function SetEvent($USERS, $type){
    global $ROOM;
    $ROOM->event->{$this->{'event_' . $type}} = true;
  }
}
