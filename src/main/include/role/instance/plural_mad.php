<?php
/*
  ◆魔女 (plural_mad)
  ○仕様
  ・魔法：占い師 (複合投票/50%)
*/
class Role_plural_mad extends Role {
  public $mix_in = ['vote' => 'plural_wizard'];
  public $action = VoteAction::PLURAL_WIZARD;
  public $result = RoleAbility::MAGE;

  public function GetPluralMageRate() {
    return 50;
  }
}
