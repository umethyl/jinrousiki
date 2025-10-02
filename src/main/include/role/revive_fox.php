<?php
/*
  ◆仙狐 (revive_fox)
  ○仕様
  ・能力結果：蘇生追加 (天啓封印あり)
  ・蘇生率：100% / 誤爆有り
  ・蘇生後：能力喪失
*/
RoleLoader::LoadFile('fox');
class Role_revive_fox extends Role_fox {
  public $mix_in = ['vote' => 'poison_cat'];

  protected function IsReviveVote() {
    return $this->IsActorActive();
  }

  protected function OutputAddResult() {
    if (DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message')) return;
    RoleHTML::OutputResult(RoleAbility::REVIVE);
  }

  protected function GetDisabledReviveVoteMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function GetReviveRate() {
    return 100;
  }

  protected function ReviveAction() {
    $this->GetActor()->LostAbility();
  }
}
