<?php
/*
  ◆酒呑童子 (sacrifice_ogre)
  ○仕様
  ・勝利：生存 + 村人陣営以外勝利
  ・仲間表示：洗脳者
  ・人攫い無効：吸血鬼陣営
  ・人攫い：洗脳者付加
  ・身代わり：洗脳者
*/
RoleManager::LoadFile('ogre');
class Role_sacrifice_ogre extends Role_ogre {
  public $mix_in = array('protected');
  public $resist_rate  =  0;
  public $reduce_base  =  3;
  public $reduce_rate  =  5;
  public $reflect_rate = 50;

  protected function OutputPartner() {
    /* 2日目の時点で洗脳者が発生する特殊イベントを実装したら対応すること */
    if (DB::$ROOM->date < 2) return;
    $stack = array();
    foreach (DB::$USER->GetRoleUser('psycho_infected') as $user) {
      $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, 'psycho_infected_list');
  }

  public function Win($winner) {
    return $winner != 'human' && $this->IsLive();
  }

  protected function IgnoreAssassin(User $user) {
    return $user->IsCamp('vampire');
  }

  protected function Assassin(User $user) {
    $user->AddRole('psycho_infected');
  }

  public function IsSacrifice(User $user) {
    return ! $this->IsActor($user) && $user->IsRole('psycho_infected');
  }
}
