<?php
/*
  ◆星妖精 (star_fairy)
  ○仕様
  ・悪戯：死亡欄妨害 (星座)
*/
RoleLoader::LoadFile('flower_fairy');
class Role_star_fairy extends Role_flower_fairy {
  protected function GetFairyActionResult() {
    return 'CONSTELLATION';
  }
}
