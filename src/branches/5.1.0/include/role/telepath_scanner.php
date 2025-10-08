<?php
/*
  ◆念騒霊 (telepath_scanner)
  ○仕様
  ・発言公開：妖狐
*/
RoleLoader::LoadFile('whisper_scanner');
class Role_telepath_scanner extends Role_whisper_scanner {
  protected function GetMindReadTargetRole() {
    return 'fox';
  }
}
