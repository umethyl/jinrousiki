<?php
/*
  ◆琴古主 (harp_mania)
  ○仕様
  ・足音：コピー先横軸
*/
RoleLoader::LoadFile('lute_mania');
class Role_harp_mania extends Role_lute_mania {
  protected function GetChainStep($id) {
    return Position::GetHorizontal($id);
  }
}
