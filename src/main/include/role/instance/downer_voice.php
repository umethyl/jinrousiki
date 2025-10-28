<?php
/*
  ◆マスク (downer_voice)
  ○仕様
  ・声量変換：下方シフト
*/
RoleLoader::LoadFile('strong_voice');
class Role_downer_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    $this->ShiftVoice($voice, $str, false);
  }
}
