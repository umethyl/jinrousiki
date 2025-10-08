<?php
/*
  ◆護衛貫通 (penetration)
  ○仕様
  ・役職表示：無し
*/
class Role_penetration extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
