<?php
/*
  ◆感染者 (infected)
  ○仕様
  ・役職表示：無し
*/
class Role_infected extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
