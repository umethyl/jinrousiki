<?php
/*
  ◆元朔狼 (changed_disguise)
  ○仕様
  ・役職表示：無し
*/
class Role_changed_disguise extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
