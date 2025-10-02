<?php
/*
  ◆交換憑依 (possessed_exchange)
  ○仕様
*/
class Role_possessed_exchange extends Role {
  protected function OutputImage() { return; }

  protected function OutputResult() {
    if (! is_array($stack = $this->GetActor()->GetPartner($this->role))) return;
    if (is_null($target = DB::$USER->ByID(array_shift($stack))->handle_name)) return;

    if (DB::$ROOM->date < 3) {
      $header = 'exchange_header';
      $footer = 'exchange_footer';
    } else {
      $header = 'partner_header';
      $target = $this->GetActor()->handle_name;
      $footer = 'possessed_target';
    }
    RoleHTML::OutputAbilityResult($header, $target, $footer);
  }
}
