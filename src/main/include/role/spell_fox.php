<?php
/*
  ◆宙狐 (spell_fox)
  ○仕様
  ・人狼襲撃カウンター：狐火 (一回限定)
*/
RoleLoader::LoadFile('fox');
class Role_spell_fox extends Role_fox {
  public function WolfEatFoxCounter(User $user) {
    if (false === $this->IsActorActive()) {
      return false;
    }
    $user->AddRole('spell_wisp');
    $this->GetActor()->LostAbility();
  }
}
