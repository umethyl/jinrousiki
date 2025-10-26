<?php
/*
  ◆天候：極光 (aurora)
  ○仕様
  ・イベント仮想役職：目隠し + 公開者 (昼限定)
*/
class Event_aurora extends Event {
  public function SetEvent() {
    $stack = DB::$ROOM->Stack()->Get('event');
    $stack->On('blinder');
    $stack->On('mind_open');
  }
}
