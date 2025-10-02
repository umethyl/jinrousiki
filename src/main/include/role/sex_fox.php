<?php
/*
  ◆雛狐 (sex_fox)
  ○仕様
  ・占い：性別鑑定
  ・呪い：無効
*/
RoleLoader::LoadFile('child_fox');
class Role_sex_fox extends Role_child_fox {
  public $mix_in = array('sex_mage');

  protected function GetMageFailed() {
    return 'mage_failed';
  }

  public function IgnoreCursed() {
    return true;
  }

  protected function GetMageResult(User $user) {
    return $this->DistinguishSex($user);
  }
}
