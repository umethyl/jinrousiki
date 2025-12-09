<?php
/*
  ◆精神鑑定士 (psycho_mage)
  ○仕様
  ・占い失敗結果：鑑定失敗
  ・呪返し：無効
  ・占い結果：精神鑑定
*/
RoleLoader::LoadFile('mage');
class Role_psycho_mage extends Role_mage {
  protected function GetMageFailed() {
    return 'mage_failed';
  }

  public function IgnoreCursed() {
    return true;
  }

  protected function GetMageResult(User $user) {
    return $this->DistinguishLiar($user);
  }

  //精神判定 (鬼陣営 > 恋人陣営 > 狂人系 > 背徳者系 > 夢能力者・不審者・無意識)
  final public function DistinguishLiar(User $user) {
    if ($user->IsMainCamp(Camp::OGRE) || $user->IsMainCamp(Camp::LOVERS)) {
      return $user->DistinguishCamp();
    } elseif ($user->IsMainGroup(CampGroup::MAD)) {
      return $this->GetLiarResult(false === $user->IsRole('swindle_mad'));
    } elseif ($user->IsMainGroup(CampGroup::DEPRAVER) || $user->IsRoleGroup('dummy')) {
      return $this->GetLiarResult(true);
    } else {
      return $this->GetLiarResult($user->IsRole('suspect', 'unconscious'));
    }
  }

  //嘘つき判定
  final public function IsLiar(User $user) {
    return $this->DistinguishLiar($user) == 'psycho_mage_liar';
  }

  //精神判定結果取得
  private function GetLiarResult($flag) {
    return (true === $flag) ? 'psycho_mage_liar' : 'psycho_mage_normal';
  }
}
