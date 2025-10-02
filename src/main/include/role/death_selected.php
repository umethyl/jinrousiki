<?php
/*
  ◆オシラ遊び (death_selected)
  ○仕様
*/
class Role_death_selected extends Role {
  protected function IgnoreAbility() { return ! $this->IsDoom(); }
}
