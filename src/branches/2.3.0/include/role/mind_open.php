<?php
/*
  ◆公開者 (mind_open)
  ○仕様
  ・発言公開：2日目以降
*/
class Role_mind_open extends Role {
  public function IsMindRead() {
    return DB::$ROOM->date > 1;
  }
}
