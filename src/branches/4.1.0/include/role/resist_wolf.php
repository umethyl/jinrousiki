<?php
/*
  ◆抗毒狼 (resist_wolf)
  ○仕様
  ・毒対象選出 (襲撃)：本人固定
  ・毒死：回避 (一回限定)
*/
RoleLoader::LoadFile('wolf');
class Role_resist_wolf extends Role_wolf {
  //処刑毒死耐性
  public function ResistVoteKillPoison() {
    return $this->IgnorePoisonDead();
  }

  public function GetPoisonEatTarget() {
    return $this->GetWolfVoter();
  }

  protected function IgnorePoisonDead() {
    $actor = $this->GetActor();
    if ($actor->IsActive()) {
      $actor->LostAbility();
      return true;
    } else {
      return false;
    }
  }
}
