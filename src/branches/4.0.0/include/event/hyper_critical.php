<?php
/*
  ◆天候：台風 (hyper_critical)
  ○仕様
  ・イベント仮想役職：会心 + 痛恨 (昼限定)
*/
class Event_hyper_critical extends Event {
  public function AddVirtualRole() {
    $method = __FUNCTION__;
    foreach (DB::$USER->Get() as $user) {
      $user->$method('critical_voter');
      $user->$method('critical_luck');
    }
  }
}
