<?php
/*
  ◆天候：スコール (grassy)
  ○仕様
  ・イベント仮想役職：草原迷彩 (昼限定)
*/
class Event_grassy extends Event {
  public function AddVirtualRole() {
    $method = __FUNCTION__;
    foreach (DB::$USER->Get() as $user) {
      $user->$method($this->name);
    }
  }
}
