<?php
/*
  ◆夢見人 (dummy_mage)
  ○仕様
  ・役職表示：占い師
  ・占い無効：天候 (熱帯夜)
  ・占い妨害：無効
  ・呪返し：無効
  ・占い結果：反転
*/
RoleLoader::LoadFile('mage');
class Role_dummy_mage extends Role_mage {
  public $display_role = 'mage';

  protected function IgnoreMage() {
    return DB::$ROOM->IsEvent('no_dream'); //天候判定 (熱帯夜)
  }

  protected function IgnoreJammer() {
    return true;
  }

  public function IgnoreCursed() {
    return true;
  }

  protected function GetMageResult(User $user) {
    return $this->DistinguishMage($user, true);
  }
}
