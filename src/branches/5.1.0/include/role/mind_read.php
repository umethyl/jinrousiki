<?php
/*
  ◆サトラレ (mind_read)
  ○仕様
  ・表示：2 日目以降
  ・発言公開：さとり (無意識は無効)
*/
class Role_mind_read extends Role {
  protected function IgnoreAbility() {
    return DateBorder::PreTwo();
  }

  public function IsMindRead() {
    return $this->GetTalkFlag('mind_read') &&
      $this->GetActor()->IsPartner($this->role, $this->GetViewer()->id) &&
      false === $this->GetActor()->IsRole('unconscious');
  }
}
