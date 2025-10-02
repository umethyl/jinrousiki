<?php
/*
  ◆内弁慶 (inside_voice)
  ○仕様
  ・声量変換：昼「小声」 / 夜「大声」固定
*/
RoleLoader::LoadFile('strong_voice');
class Role_inside_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    $voice = ArrayFilter::Pick($this->voice_list, DB::$ROOM->IsNight());
  }
}
