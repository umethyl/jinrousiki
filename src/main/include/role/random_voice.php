<?php
/*
  ◆臆病者 (random_voice)
  ○仕様
  ・声量変換：ランダム
*/
RoleLoader::LoadFile('strong_voice');
class Role_random_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    $voice = Lottery::Get($this->voice_list);
  }
}
