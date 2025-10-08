<?php
/*
  ◆悪戯 (bad_status)
  ○仕様
  ・役職表示：無し
*/
class Role_bad_status extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
