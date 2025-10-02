<?php
/*
  ◆スピーカー (speaker)
  ○仕様
  ・声の大きさが一段階大きくなり、大声は音割れしてしまう
  ・共有者の囁きは変換対象外

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
RoleLoader::LoadFile('strong_voice');
class Role_speaker extends Role_strong_voice {
  public $mix_in = array('blinder');

  public function FilterTalk(User $user, &$name, &$voice, &$str) {
    if (! $this->IgnoreTalk()) $this->ShiftVoice($voice, $str);
  }

  public function AddIgnoreTalk() {
    return ! DB::$ROOM->IsPlaying();
  }

  public function FilterWhisper(&$voice, &$str) {
    if (! $this->IgnoreTalk()) $this->ShiftVoice($voice, $str);
  }
}
