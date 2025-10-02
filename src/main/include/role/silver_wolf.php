<?php
/*
  ◆銀狼 (silver_wolf)
  ○仕様
  ・仲間表示：なし
*/
RoleManager::LoadFile('wolf');
class Role_silver_wolf extends Role_wolf {
  public function Whisper(TalkBuilder $builder, $voice) {
    return $this->WolfWhisper($builder, $voice);
  }

  //囁き (遠吠え変換)
  final public function WolfWhisper(TalkBuilder $builder, $voice) {
    return DB::$ROOM->date > 1 && $this->Howl($builder, $voice);
  }

  protected function IsWolfPartner($id) {
    return false;
  }
}
