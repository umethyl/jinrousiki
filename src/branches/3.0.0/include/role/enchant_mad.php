<?php
/*
  ◆狢 (enchant_mad)
  ○仕様
  ・悪戯：迷彩 (同一アイコン)
*/
class Role_enchant_mad extends Role {
  public $mix_in = array('vote' => 'light_fairy');
  public $bad_status = 'same_face';

  public function BadStatus(UserData $USERS) {
    if (! DB::$ROOM->IsEvent($this->bad_status)) return;

    $target = $USERS->ByID(DB::$ROOM->Stack()->Get($this->bad_status));
    if (! isset($target->icon_filename)) return;
    foreach ($USERS->rows as $user) {
      $user->icon_filename = $target->icon_filename;
    }
    DB::$ROOM->Stack()->Clear($this->bad_status);
  }
}
