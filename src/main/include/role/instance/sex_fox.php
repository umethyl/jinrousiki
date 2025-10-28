<?php
/*
  ◆雛狐 (sex_fox)
  ○仕様
  ・占い失敗結果：鑑定失敗
  ・呪返し：無効
  ・占い結果：性別鑑定
*/
RoleLoader::LoadFile('child_fox');
class Role_sex_fox extends Role_child_fox {
  public $mix_in = ['sex_mage'];

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
