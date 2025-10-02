<?php
/*
  ◆毒蝙蝠 (poison_chiroptera)
  ○仕様
  ・毒：人外カウント or 蝙蝠陣営
*/
class Role_poison_chiroptera extends Role {
  public $mix_in = array('poison');

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsInhuman($user) || $user->IsMainCamp(Camp::CHIROPTERA);
  }
}
