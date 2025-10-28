<?php
/*
  ◆幻視者 (dummy_scanner)
  ○仕様
  ・役職表示：村人
  ・能力結果：サトラレ (疑似)
  ・追加役職：なし
*/
RoleLoader::LoadFile('mind_scanner');
class Role_dummy_scanner extends Role_mind_scanner {
  public $display_role = 'human';
  public $action       = null;

  protected function IgnoreResult() {
    return DateBorder::PreTwo() || $this->GetActor()->IsRole('mind_read');
  }

  protected function OutputAddResult() {
    ImageManager::Role()->Output('mind_read');
  }

  protected function GetMindRole() {
    return null;
  }
}
