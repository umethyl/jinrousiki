<?php
/*
  ◆天狗倒し (tengu_voice)
  ○仕様
  ・声量変換：大声(確率)
*/
RoleManager::LoadFile('strong_voice');
class Role_tengu_voice extends Role_strong_voice {
  public function FilterVoice(&$voice, &$str) {
    if (Lottery::Percent(15)) $voice = 'strong';
  }
}
