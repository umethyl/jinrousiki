<?php
/*
  ◆狢 (enchant_mad)
  ○仕様
  ・悪戯：迷彩 (同一アイコン)
*/
class Role_enchant_mad extends Role {
  public $mix_in = ['vote' => 'light_fairy'];

  protected function GetBadStatus() {
    return 'same_face';
  }

  public function BadStatus() {
    $event = $this->GetBadStatus();
    if (false === DB::$ROOM->IsEvent($event)) {
      return;
    }

    $target = DB::$USER->ByID(DB::$ROOM->Stack()->Get($event));
    if (false === isset($target->icon_filename)) {
      return;
    }

    foreach (DB::$USER->Get() as $user) {
      $user->icon_filename = $target->icon_filename;
    }
    DB::$ROOM->Stack()->Clear($event);
  }
}
