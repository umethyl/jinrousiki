<?php
/*
  ◆天狗倒し (tengu_voice)
  ○仕様
  ・声量変換：大声(確率/昼限定)
*/
RoleLoader::LoadFile('strong_voice');
class Role_tengu_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    if (DB::$ROOM->IsDay() && Lottery::Percent(15)) {
      $voice = TalkVoice::STRONG;
    }
  }
}
