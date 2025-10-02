<?php
/*
  ◆吟遊詩人 (minstrel_cupid)
  ○仕様
  ・発言公開：恋人 (2日目以降)
*/
RoleLoader::LoadFile('cupid');
class Role_minstrel_cupid extends Role_cupid {
  public function IsMindRead() {
    return DB::$ROOM->date > 1 && $this->GetTalkFlag('lovers');
  }
}
