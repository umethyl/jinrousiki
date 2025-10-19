<?php
/*
  ◆星狐 (stargazer_fox)
  ○仕様
  ・呪返し：無効
  ・占い結果：投票能力鑑定
*/
RoleLoader::LoadFile('child_fox');
class Role_stargazer_fox extends Role_child_fox {
  public $mix_in = ['stargazer_mage'];

  public function IgnoreCursed() {
    return true;
  }

  protected function GetMageResult(User $user) {
    return $this->Stargazer($user);
  }
}
