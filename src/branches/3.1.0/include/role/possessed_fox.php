<?php
/*
  ◆憑狐 (possessed_fox)
  ○仕様
  ・能力結果：憑依先
  ・憑依無効陣営：人狼/恋人
*/
RoleLoader::LoadFile('fox');
class Role_possessed_fox extends Role_fox {
  public $mix_in = array('vote' => 'possessed_mad');

  protected function OutputAddResult() {
    $this->OutputPossessed();
  }

  public function IsMindReadPossessed(User $user) {
    return $this->GetTalkFlag('fox');
  }

  protected function IgnorePossessedCamp($camp) {
    return $camp == Camp::WOLF || $camp == Camp::LOVERS;
  }
}
