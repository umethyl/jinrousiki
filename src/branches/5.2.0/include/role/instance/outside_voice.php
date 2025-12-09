<?php
/*
  ◆外弁慶 (outside_voice)
  ○仕様
  ・声量変換：昼「大声」 / 夜「小声」固定
*/
RoleLoader::LoadFile('strong_voice');
class Role_outside_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    if (DB::$ROOM->IsDay()) {
      $voice = ArrayFilter::Pop($this->voice_list);
    } else {
      $voice = ArrayFilter::Pick($this->voice_list);
    }
  }
}
