<?php
/*
  ◆受信者 (mind_receiver)
  ○仕様
  ・仲間表示：対象者
  ・発言透過：対象者
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_receiver extends Role_mind_read {
  protected function GetPartner() {
    $stack = array();
    foreach ($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[$id] = DB::$USER->ByID($id)->handle_name;
    }
    ksort($stack);
    return array('mind_scanner_target' => $stack);
  }

  public function IsMindReadActive(User $user) {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $user->id);
  }
}
