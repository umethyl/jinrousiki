<?php
/*
  ◆毒橋姫 (poison_jealousy)
  ○仕様
  ・毒：恋人
*/
class Role_poison_jealousy extends Role {
  public $mix_in = 'poison';
  public $display_role = 'poison';

  public function IsPoisonTarget(User $user) {
    return $user->IsLovers();
  }
}
