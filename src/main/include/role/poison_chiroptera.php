<?php
/*
  ◆毒蝙蝠 (poison_chiroptera)
  ○仕様
  ・毒：人外カウント + 蝙蝠陣営
*/
class Role_poison_chiroptera extends Role {
  public $mix_in = 'poison';

  function IsPoisonTarget(User $user) {
    return $user->IsInhuman() || $user->IsMainCamp('chiroptera');
  }
}
