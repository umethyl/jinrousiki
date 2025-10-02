<?php
/*
  ◆イタコ (evoke_scanner)
  ○仕様
  ・追加役職：口寄せ
*/
RoleLoader::LoadFile('mind_scanner');
class Role_evoke_scanner extends Role_mind_scanner {
  protected function IsAddVote() {
    return ! DB::$ROOM->IsOpenCast();
  }

  protected function GetMindRole() {
    return 'mind_evoke';
  }

  protected function GetIgnoreAddVoteMessage() {
    return VoteRoleMessage::OPEN_CAST;
  }
}
