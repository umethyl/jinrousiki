<?php
/*
  ◆夜叉 (yaksa)
  ○仕様
  ・勝利：生存 + 人狼系全滅
  ・人攫い無効：人狼系以外
*/
RoleManager::LoadFile('ogre');
class Role_yaksa extends Role_ogre{
  public $resist_rate = 20;
  function __construct(){ parent::__construct(); }

  function Win($victory){
    if($this->IsDead() || $this->IgnoreWin($victory)) return false;
    foreach($this->GetUser() as $user){
      if($user->IsLive() && ! $this->IgnoreAssassin($user)) return false;
    }
    return true;
  }

  //勝利無効判定
  protected function IgnoreWin($victory){ return $victory == 'wolf'; }

  protected function IgnoreAssassin($user){ return ! $user->IsWolf(); }
}
