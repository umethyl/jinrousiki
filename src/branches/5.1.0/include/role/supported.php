<?php
/*
  ◆受援者 (supported)
  ○仕様
  ・役職表示：無し
*/
class Role_supported extends Role {
  protected function IgnoreImage() {
    return true;
  }
}
