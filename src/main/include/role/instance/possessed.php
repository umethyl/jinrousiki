<?php
/*
  ◆憑依 (possessed)
  ○仕様
  ・役職表示：無し
*/
class Role_possessed extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
