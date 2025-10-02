<?php
/*
  ◆交換憑依 (possessed_exchange)
  ○仕様
  ・役職表示：無し
  ・能力結果：憑依予告/憑依先
*/
class Role_possessed_exchange extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function OutputAddResult() {
    $stack = $this->GetActor()->GetPartner($this->role);
    if (false === is_array($stack)) {
      return;
    }

    $target = DB::$USER->ByID(array_shift($stack))->handle_name;
    if (null === $target) {
      return;
    }

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
