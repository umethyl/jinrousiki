<?php
/*
  ◆豆狸 (stargazer_escaper)
  ○仕様
  ・逃亡失敗：投票能力あり
*/
RoleLoader::LoadFile('escaper');
class Role_stargazer_escaper extends Role_escaper {
  public $mix_in = array('stargazer_mage');

  protected function EscapeFailed(User $user) {
    return $this->Stargazer($user) == 'stargazer_mage_ability';
  }
}
