<?php
/*
  ◆占星術師 (stargazer_mage)
  ○仕様
  ・占い：投票能力鑑定
*/
RoleManager::LoadFile('psycho_mage');
class Role_stargazer_mage extends Role_psycho_mage {
  protected function GetMageResult(User $user) {
    return $this->Stargazer($user);
  }

  //投票能力鑑定
  final public function Stargazer(User $user) {
    if ($user->ExistsVote() || $user->IsMainGroup('wolf')) {
      return 'stargazer_mage_ability';
    } else {
      return 'stargazer_mage_nothing';
    }
  }
}
