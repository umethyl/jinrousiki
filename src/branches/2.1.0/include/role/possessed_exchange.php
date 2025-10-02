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
    DB::$ROOM->date < 3 ?
      RoleHTML::OutputAbilityResult('exchange_header', $target, 'exchange_footer') :
      RoleHTML::OutputAbilityResult('partner_header', $this->GetActor()->handle_name, 'possessed_target');
  }
}
