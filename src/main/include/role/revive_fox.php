<?php
/*
  ◆仙狐 (revive_fox)
  ○仕様
  ・蘇生率：100% / 誤爆有り
  ・蘇生後：能力喪失
*/
RoleManager::LoadFile('fox');
class Role_revive_fox extends Role_fox {
  public $mix_in = array('vote' => 'poison_cat');

  protected function OutputAddResult() {
    if (DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message')) return;
    $this->OutputAbilityResult('POISON_CAT_RESULT');
  }

  public function IsReviveVote() {
    return $this->GetActor()->IsActive();
  }

  public function IgnoreReviveVoteFilter() {
    return $this->IsReviveVote() ? null : VoteRoleMessage::LOST_ABILITY;
  }

  public function GetReviveRate() {
    return 100;
  }

  public function ReviveAction() {
    $this->GetActor()->LostAbility();
  }
}
