<?php
/*
  ◆元尾先 (changed_tailtip)
  ○仕様
  ・役職表示：無し
*/
class Role_changed_tailtip extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
