<?php
/*
  ◆羊皮 (sheep_wisp)
  ○仕様
*/
class Role_sheep_wisp extends Role {
  protected function IgnoreAbility() { return ! $this->IsDoom(); }
}
