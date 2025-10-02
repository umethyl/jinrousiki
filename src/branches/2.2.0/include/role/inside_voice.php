<?php
/*
  ◆内弁慶 (inside_voice)
  ○仕様
  ・声量変換：昼「小声」 / 夜「大声」固定
*/
RoleManager::LoadFile('strong_voice');
class Role_inside_voice extends Role_strong_voice {
  function FilterVoice(&$voice, &$str) {
    $stack = $this->voice_list;
    $voice = DB::$ROOM->IsNight() ? array_pop($stack) : array_shift($stack);
  }
}
