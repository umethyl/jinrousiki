<?php
/*
  ◆憑狐 (possessed_fox)
  ○仕様
  ・憑依無効陣営：人狼/恋人
*/
RoleManager::LoadFile('fox');
class Role_possessed_fox extends Role_fox {
  public $mix_in = array('vote' => 'possessed_mad');

  protected function OutputAddResult() {
    $this->OutputPossessed();
  }

  public function IsMindReadPossessed(User $user) {
    return $this->GetTalkFlag('fox');
  }

  public function IgnorePossessed($camp) {
    return $camp == 'wolf' || $camp == 'lovers';
  }
}
