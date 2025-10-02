<?php
/*
  ◆酒呑童子
  ○仕様
  ・勝利条件：自分自身の生存 + 村人陣営以外の勝利
*/
class Role_sacrifice_ogre extends Role{
  var $resist_rate = 0;

  function Role_sacrifice_ogre(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DistinguishVictory($victory){
    return $victory != 'human' && $this->IsLive();
  }
}
