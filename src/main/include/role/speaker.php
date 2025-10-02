<?php
/*
  ◆スピーカー (speaker)
  ○仕様
  ・声の大きさが一段階大きくなり、大声は音割れしてしまう
  ・共有者の囁きは変換対象外

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
RoleManager::LoadFile('strong_voice');
class Role_speaker extends Role_strong_voice {
  public $mix_in = 'blinder';

  function IgnoreTalk() {
    return parent::IgnoreTalk() || ! DB::$ROOM->IsPlaying();
  }

  function FilterTalk(User $user, &$name, &$voice, &$str) {
    if (! $this->IgnoreTalk()) $this->ShiftVoice($voice, $str);
  }

  function FilterWhisper(&$voice, &$str) {
    if (! $this->IgnoreTalk()) $this->ShiftVoice($voice, $str);
  }
}
