<?php
/*
  ◆共鳴者 (mind_friend)
  ○仕様
  ・仲間表示：共鳴先
  ・発言透過：共鳴先
*/
class Role_mind_friend extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  protected function OutputPartner() {
    $target = $this->GetActor()->partner_list;
    $stack  = array();
    foreach(DB::$USER->rows as $user) {
      if($this->IsActor($user)) continue;
      if($user->IsPartner($this->role, $target)) $stack[$user->id] = $user->handle_name;
    }
    ksort($stack);
    RoleHTML::OutputPartner($stack, $this->role . '_list');
  }

  function IsMindRead() {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $this->GetViewer()->partner_list);
  }
}
