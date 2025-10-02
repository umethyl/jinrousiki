<?php
/*
  ◆イタコ (evoke_scanner)
  ○仕様
  ・追加役職：口寄せ
*/
RoleManager::LoadFile('mind_scanner');
class Role_evoke_scanner extends Role_mind_scanner {
  public $mind_role = 'mind_evoke';

  public function IsVote() {
    return parent::IsVote() && ! DB::$ROOM->IsOpenCast();
  }

  protected function IgnoreVoteFilter() {
    return DB::$ROOM->IsOpenCast() ? VoteRoleMessage::OPEN_CAST : null;
  }
}
