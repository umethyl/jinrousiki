<?php
/*
  ◆リアルタイム制 (real_time)
*/
class Option_real_time extends CheckRoomOptionItem {
  public $group = RoomOption::GAME_OPTION;
  public $type  = 'realtime';

  function GetCaption() { return 'リアルタイム制'; }

  function GetExplain() { return '制限時間が実時間で消費されます'; }

  function LoadPost() {
    RQ::$get->Parse('IsOn', 'post.' . $this->name);
    if (RQ::$get->{$this->name}) {
      RQ::$get->Parse('intval',
		      sprintf('post.%s_day',   $this->name),
		      sprintf('post.%s_night', $this->name));
    }
    return RQ::$get->{$this->name};
  }
}
