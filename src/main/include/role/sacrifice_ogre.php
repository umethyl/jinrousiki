<?php
/*
  ◆酒呑童子 (sacrifice_ogre)
  ○仕様
  ・勝利：生存 + 村人陣営以外勝利
  ・人攫い成功率低下：3/5
  ・仲間表示：洗脳者
  ・人攫い無効：吸血鬼陣営
  ・人攫い：洗脳者付加
  ・人狼襲撃：身代わり (無効確率 0%)
  ・身代わり：洗脳者
  ・暗殺反射確率：50%
*/
RoleLoader::LoadFile('ogre');
class Role_sacrifice_ogre extends Role_ogre {
  public $mix_in = ['protected'];

  protected function IgnorePartner() {
    /* 2日目の時点で洗脳者が発生する特殊イベントを実装したら対応すること */
    return DB::$ROOM->date < 2;
  }

  protected function GetPartner() {
    $stack = [];
    foreach (DB::$USER->GetRoleUser('psycho_infected') as $user) {
      $stack[] = $user->handle_name;
    }
    return ['psycho_infected_list' => $stack];
  }

  protected function GetOgreResistWolfEatRate() {
    return 0;
  }

  protected function IsSacrifice(User $user) {
    return false === $this->IsActor($user) && $user->IsRole('psycho_infected');
  }

  public function GetReflectAssassinRate() {
    return 50;
  }

  protected function IgnoreOgreAssassin(User $user) {
    return $user->IsCamp(Camp::VAMPIRE);
  }

  protected function GetOgreReduceNumerator() {
    return 3;
  }

  protected function OgreAssassin(User $user) {
    $user->AddRole('psycho_infected');
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::HUMAN;
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
