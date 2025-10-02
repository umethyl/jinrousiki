<?php
/*
  ◆精神鑑定士 (psycho_mage)
  ○仕様
  ・占い：精神鑑定
  ・呪い：無効
*/
RoleManager::LoadFile('mage');
class Role_psycho_mage extends Role_mage {
  public $mage_failed = 'mage_failed';

  public function IgnoreCursed() { return true; }

  protected function GetMageResult(User $user) {
    return $user->DistinguishLiar();
  }

  //精神判定
  final public function DistinguishLiar(User $user) {
    //陣営判定
    if ($user->IsMainCamp('ogre')) return 'ogre';

    //系列判定
    if ($user->IsMainGroup('mad')) { //狂人系
      return $this->GetLiarResult(! $user->IsRole('swindle_mad'));
    }
    if ($user->IsMainGroup('depraver')) return $this->GetLiarResult(true); //背徳者系

    //能力判定
    if ($user->IsRoleGroup('dummy')) return $this->GetLiarResult(true);

    //個別判定
    return $this->GetLiarResult($user->IsRole('suspect', 'unconscious'));
  }

  //精神判定結果取得
  private function GetLiarResult($flag) {
    return $flag ? 'psycho_mage_liar' : 'psycho_mage_normal';
  }
}
