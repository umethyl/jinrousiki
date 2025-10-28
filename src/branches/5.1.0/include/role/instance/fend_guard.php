<?php
/*
  ◆忍者 (fend_guard)
  ○仕様
  ・人狼襲撃耐性：無効 (一回限定)
*/
RoleLoader::LoadFile('guard');
class Role_fend_guard extends Role_guard {
  public function ResistWolfEat() {
    if (false === $this->IsActorActive()) {
      return false;
    }

    $this->GetActor()->LostAbility();
    return true;
  }
}
