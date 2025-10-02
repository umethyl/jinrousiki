<?php
/*
  ◆益荒男 (tough)
  ○仕様
  ・人狼襲撃耐性：無効 + 死の宣告
*/
class Role_tough extends Role {
  function WolfEatResist() {
    $this->GetActor()->AddDoom(1);
    return true;
  }
}
