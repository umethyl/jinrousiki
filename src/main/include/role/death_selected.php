<?php
/*
  ◆オシラ遊び (death_selected)
  ○仕様
  ・表示：当日限定
*/
class Role_death_selected extends Role {
  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }
}
