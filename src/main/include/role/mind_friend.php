<?php
/*
  ◆共鳴者 (mind_friend)
  ○仕様
  ・仲間表示：対象者
  ・発言公開：対象者
*/
RoleLoader::LoadFile('mind_read');
class Role_mind_friend extends Role_mind_read {
  protected function GetPartner() {
    $target = $this->GetActor()->GetPartnerList();
    $stack  = array();
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($this->IsActor($user)) continue;
      if ($user->IsPartner($this->role, $target)) {
	$stack[$user->id] = $user->handle_name;
      }
    }
    ksort($stack);
    return array($this->role . '_list' => $stack);
  }

  public function IsMindRead() {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $this->GetViewer()->GetPartnerList());
  }
}
