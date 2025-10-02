<?php
/*
  ◆銀狼 (silver_wolf)
  ○仕様
  ・仲間表示：なし
*/
RoleManager::LoadFile('wolf');
class Role_silver_wolf extends Role_wolf {
  function IsWolfPartner($id) { return false; }

  function Whisper(TalkBuilder $builder, $voice) {
    return DB::$ROOM->date > 1 && $this->Howl($builder, $voice);
  }
}
