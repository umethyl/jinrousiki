<?php
/*
  ◆耳栓 (earplug)
  ○仕様
  ・声の大きさが一段階小さくなり、小声は共有者の囁きに見える
  ・共有者の囁きは変換対象外

  ○問題点
  ・観戦モードにすると普通に見えてしまう
*/
RoleLoader::LoadFile('strong_voice');
class Role_earplug extends Role_strong_voice {
  public $mix_in = ['blinder'];

  public function FilterTalk(User $user, &$name, &$voice, &$str) {
    if (false === $this->IgnoreTalk()) {
      $this->ShiftVoice($voice, $str, false);
    }
  }

  public function AddIgnoreTalk() {
    return false === DB::$ROOM->IsPlaying() ||
      (DB::$ROOM->IsOn(RoomMode::LOG) && DB::$ROOM->IsEvent($this->role) && ! DB::$ROOM->IsDay());
  }

  public function FilterWhisper(&$voice, &$str) {
    if (false === $this->IgnoreTalk()) {
      $this->ShiftVoice($voice, $str, false);
    }
  }
}
