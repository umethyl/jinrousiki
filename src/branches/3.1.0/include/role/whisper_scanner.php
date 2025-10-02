<?php
/*
  ◆囁騒霊 (whisper_scanner)
  ○仕様
  ・追加役職：なし
  ・投票：なし
  ・発言公開：共有者
*/
RoleLoader::LoadFile('mind_scanner');
class Role_whisper_scanner extends Role_mind_scanner {
  public $action = null;

  protected function GetMindRole() {
    return null;
  }

  public function IsMindRead() {
    return DB::$ROOM->date > 1 && $this->GetTalkFlag($this->GetMindReadTargetRole());
  }

  //発言公開対象役職取得
  protected function GetMindReadTargetRole() {
    return 'common';
  }
}
