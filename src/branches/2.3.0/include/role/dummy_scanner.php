<?php
/*
  ◆幻視者 (dummy_scanner)
  ○仕様
  ・役職表示：村人
  ・追加役職：なし
*/
RoleManager::LoadFile('mind_scanner');
class Role_dummy_scanner extends Role_mind_scanner {
  public $display_role = 'human';
  public $action    = null;
  public $mind_role = null;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2 || $this->GetActor()->IsRole('mind_read');
  }

  protected function OutputAddResult() {
    Image::Role()->Output('mind_read');
  }
}
