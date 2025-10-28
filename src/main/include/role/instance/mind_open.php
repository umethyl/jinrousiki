<?php
/*
  ◆公開者 (mind_open)
  ○仕様
  ・表示：制限なし
  ・発言公開：2日目以降
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_open extends Role_mind_read {
  protected function IgnoreAbility() {
    return false;
  }

  public function IsMindRead() {
    return DateBorder::Second();
  }
}
