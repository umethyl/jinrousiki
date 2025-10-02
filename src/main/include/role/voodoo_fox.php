<?php
/*
  ◆九尾 (voodoo_fox)
  ○仕様
*/
RoleLoader::LoadFile('fox');
class Role_voodoo_fox extends Role_fox {
  public $mix_in = ['voodoo_mad'];
  public $action = VoteAction::VOODOO_FOX;
  public $submit = VoteAction::VOODOO_MAD;

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::WOLF, RoleAbilityMessage::VOODOO, $this->action);
  }
}
